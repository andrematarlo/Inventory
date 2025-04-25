<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Check if user exists in students table
            $student = DB::table('students')
                ->where('UserAccountID', Auth::id())
                ->first();

            if (!$student) {
                abort(403, 'Unauthorized. Only students can access this page.');
            }

            return $next($request);
        });
    }

    public function index()
    {
        $student = DB::table('students')
            ->where('UserAccountID', Auth::id())
            ->first();

        $orders = DB::table('pos_orders')
            ->where('student_id', $student->student_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pos.student.orders', compact('orders'));
    }

    public function getItems($orderId)
    {
        try {
            $student = DB::table('students')
                ->where('UserAccountID', Auth::id())
                ->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            $order = DB::table('pos_orders')
                ->where('OrderID', $orderId)
                ->where('student_id', $student->student_id)
                ->first();

            if (!$order) {
                return response()->json([
                    'error' => 'Order not found',
                    'debug' => [
                        'orderId' => $orderId,
                        'student_id' => $student->student_id
                    ]
                ], 404);
            }

            $items = DB::table('pos_order_items AS poi')
                ->select(
                    'poi.ItemName',
                    'poi.Quantity as quantity',
                    'poi.UnitPrice as price'
                )
                ->where('poi.OrderID', $orderId)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'error' => 'No items found for this order',
                    'debug' => [
                        'orderId' => $orderId,
                        'order' => $order
                    ]
                ], 404);
            }

            return response()->json(['items' => $items]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching order items',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function cancel($orderId)
    {
        try {
            $student = DB::table('students')
                ->where('UserAccountID', Auth::id())
                ->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            $order = DB::table('pos_orders')
                ->where('OrderID', $orderId)
                ->where('student_id', $student->student_id)
                ->where('Status', 'pending')
                ->first();

            if (!$order) {
                return response()->json([
                    'error' => 'Order not found or cannot be cancelled',
                    'message' => 'Only pending orders can be cancelled.'
                ], 404);
            }

            // Update order status to cancelled
            DB::table('pos_orders')
                ->where('OrderID', $orderId)
                ->update(['Status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while cancelling the order',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 