<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\POSOrder;
use App\Models\POSOrderItem;
use App\Models\Student;
use App\Models\CashDeposit;
use Illuminate\Support\Facades\DB;

class KioskController extends Controller
{
    /**
     * Display the kiosk interface.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all item classifications for the menu categories
        $classifications = Item::select('Classification')
            ->where('Status', 'Active')
            ->distinct()
            ->pluck('Classification');
            
        // Get items from the first classification for initial display
        $firstClassification = $classifications->first();
        $items = Item::where('Classification', $firstClassification)
            ->where('Status', 'Active')
            ->get();
            
        return view('pos.kiosk.index', compact('classifications', 'items'));
    }
    
    /**
     * Get items by classification for dynamic loading.
     *
     * @param  string  $classification
     * @return \Illuminate\Http\Response
     */
    public function getItemsByClassification($classification)
    {
        $items = Item::where('Classification', $classification)
            ->where('Status', 'Active')
            ->get();
            
        return response()->json($items);
    }
    
    /**
     * Place an order through the kiosk.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function placeOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:items,ItemID',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,deposit',
            'student_id' => 'required_if:payment_method,deposit|exists:students,StudentID',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Calculate total amount
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $itemData = Item::findOrFail($item['id']);
                $totalAmount += $itemData->UnitPrice * $item['quantity'];
            }
            
            // If paying with deposit, check if student has enough balance
            if ($request->payment_method === 'deposit') {
                $studentId = $request->student_id;
                $student = Student::findOrFail($studentId);
                
                // Get current balance
                $balance = CashDeposit::where('StudentID', $studentId)
                    ->sum('Amount');
                    
                if ($balance < $totalAmount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient balance in cash deposit.',
                    ], 400);
                }
            }
            
            // Create the order
            $order = new POSOrder();
            $order->OrderDate = now();
            $order->TotalAmount = $totalAmount;
            $order->PaymentMethod = $request->payment_method;
            $order->Status = 'Pending';
            
            if ($request->payment_method === 'deposit') {
                $order->StudentID = $request->student_id;
            }
            
            $order->save();
            
            // Add order items
            foreach ($request->items as $item) {
                $itemData = Item::findOrFail($item['id']);
                
                $orderItem = new POSOrderItem();
                $orderItem->OrderID = $order->OrderID;
                $orderItem->ItemID = $item['id'];
                $orderItem->Quantity = $item['quantity'];
                $orderItem->UnitPrice = $itemData->UnitPrice;
                $orderItem->Subtotal = $itemData->UnitPrice * $item['quantity'];
                $orderItem->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully!',
                'order_id' => $order->OrderID,
                'order_number' => str_pad($order->OrderID, 6, '0', STR_PAD_LEFT),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order: ' . $e->getMessage(),
            ], 500);
        }
    }
} 