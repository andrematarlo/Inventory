<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\POSOrder;
use App\Models\POSOrderItem;
use App\Models\CashDeposit;
use App\Models\Item;
use App\Models\Student;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CashierController extends Controller
{
    /**
     * Display the cashiering interface.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pendingOrders = POSOrder::with(['items', 'student'])
            ->where('Status', 'Pending')
            ->orderBy('OrderDate', 'desc')
            ->get();
            
        return view('pos.cashier.index', compact('pendingOrders'));
    }
    
    /**
     * Get all pending orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function getOrders()
    {
        $pendingOrders = POSOrder::with(['items', 'student'])
            ->where('Status', 'Pending')
            ->orderBy('OrderDate', 'desc')
            ->get();
            
        return response()->json($pendingOrders);
    }
    
    /**
     * Process payment for an order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:pos_orders,OrderID',
            'payment_method' => 'required|in:cash,deposit',
            'amount_tendered' => 'required_if:payment_method,cash|numeric|min:0',
        ]);
        
        try {
            DB::beginTransaction();
            
            $order = POSOrder::with('items')->findOrFail($request->order_id);
            
            // Check if order is already processed
            if ($order->Status !== 'Pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This order has already been processed.',
                ], 400);
            }
            
            // For cash payment
            if ($request->payment_method === 'cash') {
                // Check if amount tendered is enough
                if ($request->amount_tendered < $order->TotalAmount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Amount tendered is less than the total amount.',
                    ], 400);
                }
                
                $order->AmountTendered = $request->amount_tendered;
                $order->Change = $request->amount_tendered - $order->TotalAmount;
            } 
            // For deposit payment
            else if ($request->payment_method === 'deposit') {
                if (!$order->StudentID) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No student associated with this order for deposit payment.',
                    ], 400);
                }
                
                // Check student balance
                $balance = CashDeposit::where('StudentID', $order->StudentID)
                    ->sum('Amount');
                    
                if ($balance < $order->TotalAmount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient balance in student account.',
                    ], 400);
                }
                
                // Create negative deposit record to deduct balance
                $deposit = new CashDeposit();
                $deposit->StudentID = $order->StudentID;
                $deposit->Amount = -$order->TotalAmount; // Negative amount for deduction
                $deposit->TransactionDate = now();
                $deposit->Description = 'Payment for Order #' . str_pad($order->OrderID, 6, '0', STR_PAD_LEFT);
                $deposit->TransactionType = 'Payment';
                $deposit->ProcessedBy = Auth::id();
                $deposit->save();
                
                $order->AmountTendered = $order->TotalAmount;
                $order->Change = 0;
            }
            
            // Update order status and process inventory
            $order->Status = 'Completed';
            $order->PaymentMethod = $request->payment_method;
            $order->CompletedDate = now();
            $order->ProcessedBy = Auth::id();
            $order->save();
            
            // Update inventory for each item
            foreach ($order->items as $orderItem) {
                $item = Item::findOrFail($orderItem->ItemID);
                
                // Subtract from inventory
                $item->Quantity -= $orderItem->Quantity;
                $item->save();
                
                // Create inventory transaction record
                $transaction = new InventoryTransaction();
                $transaction->ItemID = $orderItem->ItemID;
                $transaction->QuantityChange = -$orderItem->Quantity; // Negative for outgoing
                $transaction->TransactionDate = now();
                $transaction->TransactionType = 'POS Sale';
                $transaction->ReferenceID = $order->OrderID;
                $transaction->Remarks = 'Sold via POS Order #' . str_pad($order->OrderID, 6, '0', STR_PAD_LEFT);
                $transaction->ProcessedBy = Auth::id();
                $transaction->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully.',
                'order_id' => $order->OrderID,
                'receipt_url' => route('pos.cashier.receipt', $order->OrderID),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Generate a receipt for a completed order.
     *
     * @param  int  $order
     * @return \Illuminate\Http\Response
     */
    public function generateReceipt($order)
    {
        $order = POSOrder::with(['items.item', 'student', 'processedBy'])
            ->findOrFail($order);
            
        if ($order->Status !== 'Completed') {
            return redirect()->route('pos.cashier.index')
                ->with('error', 'Cannot generate receipt for an incomplete order.');
        }
        
        return view('pos.cashier.receipt', compact('order'));
    }
} 