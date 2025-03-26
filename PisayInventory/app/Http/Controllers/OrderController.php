<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('items')
            ->withCount('items')
            ->orderBy('OrderDate', 'desc')
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
        $order = Order::with(['items.menuItem'])->findOrFail($id);
        
        return view('pos.orders.partials.items', compact('order'));
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
} 