<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Classification;
use Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['items', 'student'])
                      ->orderBy('created_at', 'desc')
                      ->paginate(15);
        return view('pos.orders.index', compact('orders'));
    }

    public function create()
    {
        $menuItems = MenuItem::where('IsDeleted', 0)
                           ->where('StocksAvailable', '>', 0)
                           ->get();
        $categories = Classification::where('IsDeleted', 0)->get();
        
        // Get student balance if authenticated user is a student
        $studentBalance = 0;
        if (Auth::check() && Auth::user()->role === 'Students') {
            $studentBalance = Auth::user()->getBalance();
        }
        
        return view('pos.orders.create', compact('menuItems', 'categories', 'studentBalance'));
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
} 