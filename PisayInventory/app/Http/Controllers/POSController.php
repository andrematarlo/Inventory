<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\Classification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class POSController extends Controller
{
    public function index()
    {
        $menuItems = DB::table('menu_items')
                        ->where('IsDeleted', 0)
                        ->where('IsAvailable', 1)
                        ->get();
                        
        $categories = DB::table('classification')
                        ->where('IsDeleted', 0)
                        ->get();
        
        // Check if logged in user is a student and get their balance
        $isStudent = Auth::check() && Auth::user()->role === 'Student';
        $studentBalance = 0;
        $studentId = null;
        
        if ($isStudent) {
            $studentId = Auth::user()->student_id;
            $studentBalance = DB::table('student_deposits')
                ->where('student_id', $studentId)
                ->where('status', 'Active')
                ->orderBy('created_at', 'desc')
                ->value('balance_after') ?? 0;
        }
        
        return view('pos.index', compact('menuItems', 'categories', 'isStudent', 'studentBalance', 'studentId'));
    }
    
    public function create()
    {
        $menuItems = DB::table('menu_items')
                        ->where('IsDeleted', 0)
                        ->where('IsAvailable', 1)
                        ->get();
        
        // Debug information                
        if ($menuItems->isEmpty()) {
            // If no menu items found, check if there are any items at all
            $allItems = DB::table('menu_items')->get();
            
            if ($allItems->isEmpty()) {
                // No items at all in the table
                session()->flash('info', 'No menu items found in the database. Please add items to display them here.');
            } else {
                // Items exist but none match the criteria
                $deletedCount = DB::table('menu_items')->where('IsDeleted', 1)->count();
                $unavailableCount = DB::table('menu_items')->where('IsAvailable', 0)->count();
                
                session()->flash('info', "Found {$allItems->count()} total items, but {$deletedCount} are deleted and {$unavailableCount} are marked as unavailable.");
            }
        }
                        
        $categories = DB::table('classification')
                        ->where('IsDeleted', 0)
                        ->get();
        
        return view('pos.create', compact('menuItems', 'categories'));
    }
    
    public function store(Request $request)
    {
        // Log raw $_POST data
        \Illuminate\Support\Facades\Log::info('Raw $_POST data', [
            'post_data' => $_POST
        ]);
        
        // More detailed debug log
        \Illuminate\Support\Facades\Log::info('Order submission received', [
            'payment_type' => $request->payment_type,
            'student_id' => $request->student_id,
            'total_amount' => $request->total_amount,
            'has_cart_items' => isset($request->cart_items),
            'cart_items_type' => isset($request->cart_items) ? gettype($request->cart_items) : 'not set',
            'cart_items_raw' => $request->cart_items,
            'all_request_data' => $request->all()
        ]);
        
        // Try to decode JSON if it's a string
        if (isset($request->cart_items) && is_string($request->cart_items)) {
            try {
                $decodedItems = json_decode($request->cart_items, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $request->merge(['cart_items' => $decodedItems]);
                    \Illuminate\Support\Facades\Log::info('Successfully decoded cart_items JSON', [
                        'decoded_items' => $decodedItems
                    ]);
                } else {
                    \Illuminate\Support\Facades\Log::error('JSON decode error', [
                        'error' => json_last_error_msg()
                    ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Exception decoding JSON', [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Validate the request data
        try {
            $validated = $request->validate([
                'payment_type' => 'required|in:cash,card,deposit',
                'student_id' => 'required_if:payment_type,deposit|nullable|exists:students,student_id',
                'total_amount' => 'required|numeric|min:0',
                'cart_items' => 'required|array',
                'cart_items.*.id' => 'nullable',  // Can be null for custom items
                'cart_items.*.name' => 'required|string',
                'cart_items.*.price' => 'required|numeric|min:0',
                'cart_items.*.quantity' => 'required|integer|min:1',
            ]);
            
            \Illuminate\Support\Facades\Log::info('Validation passed', [
                'validated_data' => $validated
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Validation failed', [
                'errors' => $e->errors()
            ]);
            throw $e;  // Re-throw to let Laravel handle it normally
        }

        try {
            DB::beginTransaction();

            // Create order with StudentId field
            $orderData = [
                'OrderNumber' => 'ORD-' . time(),
                'TotalAmount' => $request->total_amount,
                'PaymentType' => $request->payment_type,
                'Status' => 'pending',
                'CreatedById' => Auth::id() ?? 1,
                'DateCreated' => now()
            ];
            
            // Add StudentId if provided (required for deposit payments)
            if ($request->has('student_id') && !empty($request->student_id)) {
                $orderData['StudentId'] = $request->student_id;
            }

            $orderId = DB::table('orders')->insertGetId($orderData);

            // Create order items
            foreach ($request->cart_items as $item) {
                DB::table('order_items')->insert([
                    'OrderId' => $orderId,
                    'ItemId' => $item['id'],
                    'Quantity' => $item['quantity'],
                    'UnitPrice' => $item['price'],
                    'Subtotal' => $item['price'] * $item['quantity'],
                    'CreatedById' => Auth::id() ?? 1,
                    'DateCreated' => now()
                ]);

                // Update stock only for non-custom items
                if (!isset($item['isCustom']) || !$item['isCustom']) {
                    DB::table('menu_items')
                        ->where('ItemId', $item['id'])
                        ->decrement('StocksAvailable', $item['quantity']);
                }
            }

            // Create payment transaction
            DB::table('payment_transactions')->insert([
                'OrderId' => $orderId,
                'Amount' => $request->total_amount,
                'PaymentType' => $request->payment_type,
                'Status' => 'pending',
                'ReferenceNumber' => 'PAY-' . time(),
                'CreatedById' => Auth::id() ?? 1,
                'DateCreated' => now()
            ]);

            DB::commit();
            return redirect()->route('pos.index')->with('success', 'Order processed successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to process order: ' . $e->getMessage());
        }
    }
    
    public function filterByCategory($categoryId)
    {
        $menuItems = DB::table('menu_items')
                        ->where('IsDeleted', 0)
                        ->where('IsAvailable', 1);
                        
        if ($categoryId) {
            $menuItems = $menuItems->where('ClassificationId', $categoryId);
        }
        
        $menuItems = $menuItems->get();
        
        return response()->json($menuItems);
    }
    
    // Cashiering function - for processing payments
    public function cashiering()
    {
        $pendingOrders = DB::table('orders')
                           ->where('IsDeleted', 0)
                           ->where('Status', 'pending')
                           ->orderBy('DateCreated', 'desc')
                           ->get();
                           
        return view('pos.cashiering', compact('pendingOrders'));
    }
    
    // Cash Deposit function - for managing deposits
    public function cashDeposit()
    {
        $isStudent = Auth::check() && Auth::user()->role === 'Student';
        
        // If student is logged in, show only their own deposits
        if ($isStudent) {
            $studentId = Auth::user()->student_id;
            $pendingDeposits = DB::table('student_deposits')
                ->where('student_id', $studentId)
                ->whereRaw('LOWER(status) = ?', ['pending'])
                ->orderBy('created_at', 'desc')
                ->get();
                
            $approvedDeposits = DB::table('student_deposits')
                ->where('student_id', $studentId)
                ->whereRaw('LOWER(status) = ?', ['active'])
                ->orderBy('created_at', 'desc')
                ->get();
                
            // Get current balance
            $currentBalance = DB::table('student_deposits')
                ->where('student_id', $studentId)
                ->whereRaw('LOWER(status) = ?', ['active'])
                ->orderBy('created_at', 'desc')
                ->value('balance_after') ?? 0;
                
            return view('pos.cashdeposit', compact('pendingDeposits', 'approvedDeposits', 'currentBalance', 'isStudent', 'studentId'));
        }
        
        // For cashiers and admins, show all pending deposits that need approval
        $pendingDeposits = DB::table('student_deposits')
            ->join('students', function($join) {
                $join->on(DB::raw('student_deposits.student_id COLLATE utf8mb4_unicode_ci'), '=', DB::raw('students.student_id COLLATE utf8mb4_unicode_ci'));
            })
            ->select('student_deposits.*', 'students.first_name as FirstName', 'students.last_name as LastName', 'students.grade_level as Grade', 'students.section as Section')
            ->whereRaw('LOWER(student_deposits.status) = ?', ['pending'])
            ->orderBy('student_deposits.created_at', 'desc')
            ->get();
            
        // Debug info for admin view
        $allDeposits = DB::table('student_deposits')
            ->select('student_id', 'status', 'amount', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('pos.cashdeposit', compact('pendingDeposits', 'isStudent', 'allDeposits'));
    }
    
    // Handle deposit creation
    public function storeDeposit(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string|exists:students,student_id',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            
            // Check if student exists
            $student = DB::table('students')
                ->where('student_id', $request->student_id)
                ->first();
                
            if (!$student) {
                return redirect()->back()->with('error', 'Student not found with ID: ' . $request->student_id);
            }
            
            // Get current balance
            $currentBalance = DB::table('student_deposits')
                ->where('student_id', $request->student_id)
                ->whereRaw('LOWER(status) = ?', ['active'])
                ->orderBy('created_at', 'desc')
                ->value('balance_after') ?? 0;
                
            // For students, create a pending deposit
            $isStudent = Auth::check() && Auth::user()->role === 'Student';
            $status = $isStudent ? 'pending' : 'active';
            
            // If it's an admin or cashier creating the deposit, it's immediately active
            // and the balance is updated
            $newBalance = $status === 'active' ? $currentBalance + $request->amount : $currentBalance;
            
            // Create deposit record
            DB::table('student_deposits')->insert([
                'student_id' => $request->student_id,
                'transaction_date' => now(),
                'reference_number' => 'DEP-' . time(),
                'transaction_type' => 'Deposit',
                'amount' => $request->amount,
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
                'status' => $status
            ]);
            
            DB::commit();
            
            if ($status === 'pending') {
                return redirect()->route('pos.cashdeposit')
                    ->with('success', 'Deposit request of ₱' . number_format($request->amount, 2) . ' has been submitted and is pending approval.');
            } else {
                return redirect()->route('pos.cashdeposit')
                    ->with('success', 'Deposit of ₱' . number_format($request->amount, 2) . ' was successfully added for student ' . $student->first_name . ' ' . $student->last_name);
            }
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to process deposit: ' . $e->getMessage());
        }
    }
    
    // Approve pending deposits (for cashiers/admins)
    public function approveDeposit(Request $request, $depositId)
    {
        try {
            DB::beginTransaction();
            
            // Get the deposit
            $deposit = DB::table('student_deposits')
                ->where('deposit_id', $depositId)
                ->whereRaw('LOWER(status) = ?', ['pending'])
                ->first();
                
            if (!$deposit) {
                return redirect()->back()->with('error', 'Deposit not found or already processed.');
            }
            
            // Get current balance
            $currentBalance = DB::table('student_deposits')
                ->where('student_id', $deposit->student_id)
                ->whereRaw('LOWER(status) = ?', ['active'])
                ->orderBy('created_at', 'desc')
                ->value('balance_after') ?? 0;
                
            // Calculate new balance
            $newBalance = $currentBalance + $deposit->amount;
            
            // Update the deposit
            DB::table('student_deposits')
                ->where('deposit_id', $depositId)
                ->update([
                    'status' => 'active',
                    'balance_before' => $currentBalance,
                    'balance_after' => $newBalance,
                    'updated_at' => now()
                ]);
                
            DB::commit();
            
            // Get student info for the success message
            $student = DB::table('students')
                ->where('student_id', $deposit->student_id)
                ->first();
                
            return redirect()->route('pos.cashdeposit')
                ->with('success', 'Deposit of ₱' . number_format($deposit->amount, 2) . ' for ' . $student->first_name . ' ' . $student->last_name . ' has been approved and added to their balance.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to approve deposit: ' . $e->getMessage());
        }
    }
    
    // Reject pending deposits (for cashiers/admins)
    public function rejectDeposit(Request $request, $depositId)
    {
        try {
            DB::beginTransaction();
            
            // Get the deposit first
            $deposit = DB::table('student_deposits')
                ->where('deposit_id', $depositId)
                ->whereRaw('LOWER(status) = ?', ['pending'])
                ->first();
                
            if (!$deposit) {
                return redirect()->back()->with('error', 'Deposit not found or already processed.');
            }
            
            // Update the deposit status to rejected
            DB::table('student_deposits')
                ->where('deposit_id', $depositId)
                ->update([
                    'status' => 'rejected',
                    'notes' => $request->has('rejection_reason') 
                        ? $deposit->notes . ' | Rejected: ' . $request->rejection_reason
                        : $deposit->notes . ' | Rejected by admin',
                    'updated_at' => now()
                ]);
                
            DB::commit();
            
            return redirect()->route('pos.cashdeposit')
                ->with('success', 'Deposit request has been rejected.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to reject deposit: ' . $e->getMessage());
        }
    }
    
    // Get current student balance (for AJAX calls)
    public function getStudentBalance($studentId)
    {
        $balance = DB::table('student_deposits')
            ->where('student_id', $studentId)
            ->where('status', 'Active')
            ->orderBy('created_at', 'desc')
            ->value('balance_after') ?? 0;
            
        return response()->json(['balance' => $balance]);
    }
    
    // POS Reports function
    public function reports()
    {
        // Get sales data for reporting
        $salesData = DB::table('payment_transactions')
                       ->where('Status', 'completed')
                       ->select(
                           DB::raw('DATE(DateCreated) as date'),
                           DB::raw('SUM(Amount) as total_sales'),
                           DB::raw('COUNT(*) as transaction_count')
                       )
                       ->groupBy('date')
                       ->orderBy('date', 'desc')
                       ->limit(30)
                       ->get();
                       
        return view('pos.reports', compact('salesData'));
    }
    
    // Process payment for an order
    public function processPayment(Request $request, $orderId)
    {
        $request->validate([
            'payment_amount' => 'required|numeric',
            'payment_type' => 'required|in:cash,deposit'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Get the order details
            $order = DB::table('orders')
                ->where('OrderId', $orderId)
                ->first();
                
            if (!$order) {
                return redirect()->back()->with('error', 'Order not found.');
            }
            
            // Handle deposit payment
            if ($request->payment_type === 'deposit') {
                // Verify student ID is present in the order
                if (empty($order->StudentId)) {
                    return redirect()->back()->with('error', 'Student ID is required for deposit payments.');
                }
                
                // Get student's current balance
                $currentBalance = DB::table('student_deposits')
                    ->where('student_id', $order->StudentId)
                    ->where('status', 'Active')
                    ->orderBy('created_at', 'desc')
                    ->value('balance_after') ?? 0;
                    
                // Check if student has enough balance
                if ($currentBalance < $order->TotalAmount) {
                    return redirect()->back()->with('error', 'Insufficient balance. Current balance: ₱' . number_format($currentBalance, 2));
                }
                
                // Calculate new balance
                $newBalance = $currentBalance - $order->TotalAmount;
                
                // Record the payment as a deduction from deposit balance
                DB::table('student_deposits')->insert([
                    'student_id' => $order->StudentId,
                    'transaction_date' => now(),
                    'reference_number' => 'PAY-' . $order->OrderNumber,
                    'transaction_type' => 'Payment',
                    'amount' => -$order->TotalAmount, // Negative amount for deduction
                    'balance_before' => $currentBalance,
                    'balance_after' => $newBalance,
                    'notes' => 'Payment for Order #' . $order->OrderNumber,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'status' => 'Active'
                ]);
            }
            
            // Update order status
            DB::table('orders')
                ->where('OrderId', $orderId)
                ->update([
                    'Status' => 'paid',
                    'ModifiedById' => Auth::id() ?? 1,
                    'DateModified' => now()
                ]);
                
            // Update payment transaction
            DB::table('payment_transactions')
                ->where('OrderId', $orderId)
                ->update([
                    'Status' => 'completed',
                    'Amount' => $request->payment_amount,
                    'ModifiedById' => Auth::id() ?? 1,
                    'DateModified' => now()
                ]);
                
            DB::commit();
            return redirect()->route('pos.cashiering')->with('success', 'Payment processed successfully');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    // Add a menu item quickly
    public function addMenuItem(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:classification,ClassificationId',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            
            // Create menu item
            $menuItemId = DB::table('menu_items')->insertGetId([
                'ItemName' => $request->item_name,
                'Price' => $request->price,
                'ClassificationId' => $request->category_id,
                'Description' => $request->description,
                'StocksAvailable' => 100, // Default stock
                'IsAvailable' => 1,
                'IsDeleted' => 0,
                'CreatedById' => Auth::id() ?? 1,
                'DateCreated' => now()
            ]);
            
            DB::commit();
            
            return redirect()->route('pos.create')
                ->with('success', "Menu item '{$request->item_name}' added successfully!");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to add menu item: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    // Cancel an order
    public function cancelOrder($orderId)
    {
        try {
            DB::beginTransaction();
            
            // Get the order
            $order = DB::table('orders')
                ->where('OrderId', $orderId)
                ->where('Status', 'pending') // Only pending orders can be cancelled
                ->where('IsDeleted', 0)
                ->first();
                
            if (!$order) {
                return redirect()->back()->with('error', 'Order not found or cannot be cancelled');
            }
            
            // Update order status
            DB::table('orders')
                ->where('OrderId', $orderId)
                ->update([
                    'Status' => 'cancelled',
                    'ModifiedById' => Auth::id() ?? 1,
                    'DateModified' => now()
                ]);
                
            // Restore stock for each order item
            $orderItems = DB::table('order_items')
                ->where('OrderId', $orderId)
                ->get();
                
            foreach ($orderItems as $item) {
                // Only increment stock for regular inventory items
                DB::table('menu_items')
                    ->where('ItemId', $item->ItemId)
                    ->increment('StocksAvailable', $item->Quantity);
            }
            
            // Update payment transaction
            DB::table('payment_transactions')
                ->where('OrderId', $orderId)
                ->update([
                    'Status' => 'cancelled',
                    'ModifiedById' => Auth::id() ?? 1,
                    'DateModified' => now()
                ]);
                
            DB::commit();
            
            return redirect()->route('pos.index')->with('success', 'Order has been cancelled successfully');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }
    
    // Get order details (for AJAX)
    public function getOrderDetails($orderId)
    {
        try {
            $order = DB::table('orders')
                ->leftJoin('students', 'orders.StudentId', '=', 'students.student_id')
                ->where('orders.OrderId', $orderId)
                ->where('orders.IsDeleted', 0)
                ->select('orders.*', 'students.first_name as FirstName', 'students.last_name as LastName', 'students.student_id as StudentNumber')
                ->first();
                
            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }
            
            // Get order items
            $items = DB::table('order_items')
                ->leftJoin('menu_items', 'order_items.ItemId', '=', 'menu_items.ItemId')
                ->where('order_items.OrderId', $orderId)
                ->select(
                    'order_items.*',
                    'menu_items.ItemName',
                    'menu_items.ImagePath'
                )
                ->get();
                
            return response()->json([
                'order' => $order,
                'items' => $items
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve order details: ' . $e->getMessage()], 500);
        }
    }

    // Search students for Select2 dropdown
    public function searchStudents(Request $request)
    {
        $term = $request->input('term');
        $page = $request->input('page', 1);
        $resultsPerPage = 10;
        
        $query = DB::table('students')
            ->where('status', 'Active')
            ->where(function($query) use ($term) {
                if (!empty($term)) {
                    $query->where('student_id', 'like', '%' . $term . '%')
                        ->orWhere('first_name', 'like', '%' . $term . '%')
                        ->orWhere('last_name', 'like', '%' . $term . '%');
                }
            })
            ->select('student_id as id', 
                    DB::raw("CONCAT(first_name, ' ', last_name, ' (', student_id, ')') as text"),
                    'first_name', 'last_name', 'grade_level', 'section');
                    
        $total = $query->count();
        $lastPage = ceil($total / $resultsPerPage);
        
        $results = $query
            ->skip(($page - 1) * $resultsPerPage)
            ->take($resultsPerPage)
            ->get();
            
        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $page < $lastPage
            ]
        ]);
    }
} 