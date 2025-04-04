<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Classification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\OrderItem;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['items', 'student'])
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('pos.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['items.menuItem', 'student'])
            ->findOrFail($id);
            
        return view('pos.orders.show', compact('order'));
    }

    public function getItems($id)
    {
        try {
            $order = Order::with('items')->findOrFail($id);
            return view('pos.orders.partials.items', compact('order'));
        } catch (\Exception $e) {
            return response()->view('pos.orders.partials.items', [
                'error' => 'Error loading order items'
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->Status = $request->status;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating order status'
            ], 500);
        }
    }

    public function print($id)
    {
        $order = Order::with(['items.menuItem', 'student'])
            ->findOrFail($id);
            
        return view('pos.orders.print', compact('order'));
    }

    public function create()
    {
        $menuItems = MenuItem::where('IsDeleted', false)
            ->where('IsAvailable', true)
            ->with('classification')
            ->get();
        
        $categories = Classification::all();
        
        return view('pos.create', compact('menuItems', 'categories'));
    }

    public function store(Request $request)
    {
        try {
            \DB::beginTransaction();

            $order = Order::create([
                'student_id' => Auth::user()->student_id,
                'TotalAmount' => $request->total_amount,
                'PaymentMethod' => $request->payment_type,
                'Status' => 'pending',
                'OrderNumber' => 'ORD-' . date('Ymd') . '-' . str_pad(
                    Order::whereDate('created_at', today())->count() + 1, 
                    4, 
                    '0', 
                    STR_PAD_LEFT
                ),
                'ProcessedBy' => Auth::id(),
                'ProcessedAt' => now(),
                'AmountTendered' => $request->amount_tendered,
                'ChangeAmount' => $request->change_amount
            ]);

            // Add order items
            $cartItems = json_decode($request->cart_items, true);
            foreach ($cartItems as $item) {
                $order->items()->attach($item['id'], [
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // Update stock
                $menuItem = MenuItem::find($item['id']);
                $menuItem->StocksAvailable -= $item['quantity'];
                $menuItem->save();
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'order_number' => $order->OrderNumber,
                'alert' => [
                    'title' => 'Order Created',
                    'text' => 'Your order has been created successfully.'
                ]
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $order = Order::with(['items.menuItem'])->findOrFail($id);
        return view('pos.orders.edit', compact('order'));
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $order = Order::findOrFail($id);
            
            $validated = $request->validate([
                'Status' => 'required|in:pending,paid,preparing,ready,cancelled',
                'PaymentMethod' => 'required|in:cash,deposit',
                'AmountTendered' => 'nullable|numeric|min:0',
                'Remarks' => 'nullable|string'
            ]);

            // Update status
            $order->Status = $validated['Status'];
            
            // Update payment method and related fields
            $order->PaymentMethod = $validated['PaymentMethod'];
            $order->AmountTendered = $validated['AmountTendered'];
            $order->Remarks = $validated['Remarks'];
            
            // Calculate change amount if cash payment
            if ($order->PaymentMethod === 'cash' && $order->AmountTendered > 0) {
                $order->ChangeAmount = $order->AmountTendered - $order->TotalAmount;
            }

            // Update processed by and time if status is changing to paid or beyond
            if (in_array($validated['Status'], ['paid', 'preparing', 'ready'])) {
                $order->ProcessedBy = Auth::id();
                $order->ProcessedAt = now();
            }

            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->delete();  // This will soft delete since we're using SoftDeletes

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDetails($id)
    {
        try {
            $order = Order::with(['items', 'student'])->findOrFail($id);
            
            return response()->json([
                'error' => false,
                'order' => $order,
                'items' => $order->items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error loading order details'
            ], 500);
        }
    }

    public function dashboard()
    {
        $preparingOrders = Order::with('student')
            ->where('Status', 'preparing')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                $order->items_count = $order->items()->count();
                return $order;
            });

        $readyOrders = Order::with('student')
            ->where('Status', 'ready')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                $order->items_count = $order->items()->count();
                return $order;
            });

        return view('pos.dashboard', compact('preparingOrders', 'readyOrders'));
    }

    public function getOrderItems($id)
    {
        $order = Order::findOrFail($id);
        $items = $order->items()->with('item')->get()->map(function ($orderItem) {
            return [
                'ItemName' => $orderItem->item->ItemName,
                'Quantity' => $orderItem->Quantity,
                'UnitPrice' => $orderItem->UnitPrice,
                'Subtotal' => $orderItem->Subtotal
            ];
        });

        return response()->json(['items' => $items]);
    }

    public function processById($id)
    {
        try {
            $order = Order::findOrFail($id);
            
            // Check if order is already processed
            if ($order->Status === Order::STATUS_READY) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already ready to serve.'
                ], 400);
            }

            // Update order status
            $order->Status = Order::STATUS_READY;
            $order->ProcessedBy = auth()->id();
            $order->ProcessedAt = now();
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order marked as ready to serve.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function claim($id)
    {
        try {
            $order = Order::findOrFail($id);
            
            // Check if order is already claimed
            if ($order->Status === Order::STATUS_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order has already been claimed.'
                ], 400);
            }

            // Update order status to completed
            $order->Status = Order::STATUS_COMPLETED;
            $order->ProcessedBy = auth()->id();
            $order->ProcessedAt = now();
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order has been claimed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error claiming order: ' . $e->getMessage()
            ], 500);
        }
    }
} 