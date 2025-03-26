<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Classification;
use Auth;
use App\Models\OrderItem;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['items', 'customer'])
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('pos.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['items.menuItem', 'customer'])
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
        $order = Order::with(['items.menuItem', 'customer'])
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
                'total_amount' => $request->total_amount,
                'payment_type' => $request->payment_type,
                'amount_tendered' => $request->amount_tendered,
                'change_amount' => $request->change_amount,
                'status' => 'Completed',
                'created_by' => Auth::id()
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
                'order_number' => $order->id,
                'alert' => [
                    'title' => 'Order Completed',
                    'text' => 'Your order has been successfully processed.'
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
            $order = Order::findOrFail($id);
            
            $validated = $request->validate([
                'Status' => 'required|in:pending,completed,cancelled',
                'PaymentMethod' => 'required|in:cash,deposit',
                'AmountTendered' => 'nullable|numeric|min:0',
                'Remarks' => 'nullable|string'
            ]);

            $order->Status = $validated['Status'];
            $order->PaymentMethod = $validated['PaymentMethod'];
            $order->AmountTendered = $validated['AmountTendered'];
            $order->Remarks = $validated['Remarks'];
            
            if ($order->PaymentMethod === 'cash' && $order->AmountTendered > 0) {
                $order->ChangeAmount = $order->AmountTendered - $order->TotalAmount;
            }

            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully'
            ]);
        } catch (\Exception $e) {
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
            $order = Order::with(['items', 'customer'])->findOrFail($id);
            
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
} 