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
use Illuminate\Support\Facades\Log;
use App\Models\POSOrder;
use App\Models\POSOrderItem;
use App\Models\Student;
use App\Models\CashDeposit;
use Exception;

class POSController extends Controller
{
    public function index(Request $request)
    {
        $query = POSOrder::with(['student', 'items'])
            ->orderBy('created_at', 'desc');
            
        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('OrderNumber', 'LIKE', "%{$search}%")
                  ->orWhereHas('student', function($q2) use ($search) {
                      $q2->where('first_name', 'LIKE', "%{$search}%")
                         ->orWhere('last_name', 'LIKE', "%{$search}%");
                  })
                  ->orWhere('Status', 'LIKE', "%{$search}%")
                  ->orWhere('TotalAmount', 'LIKE', "%{$search}%");
            });
        }
        
        $orders = $query->paginate(10)->withQueryString();

        // Get student info if logged in user is a student
        $isStudent = Auth::check() && Auth::user()->role === 'Student';
        $studentBalance = 0;
        $studentId = null;
        
        if ($isStudent) {
            $studentId = Auth::user()->student_id;
            $studentBalance = CashDeposit::where('student_id', $studentId)
                ->whereNull('deleted_at')
                ->sum('Amount');
        }

        return view('pos.index', compact('orders', 'isStudent', 'studentBalance', 'studentId'));
    }
    
    public function create()
    {
        $menuItems = MenuItem::active()
                        ->with(['classification', 'unitOfMeasure'])
                        ->get();
                        
        $categories = Classification::where('IsDeleted', false)
                      ->orderBy('ClassificationName')
                      ->get();
        
        return view('pos.create', compact('menuItems', 'categories'));
    }
    
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            // Parse cart items from JSON string if needed
            $cartItems = $request->items;
            if (!is_array($cartItems) && $request->has('cart_items')) {
                $cartItems = json_decode($request->cart_items, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid cart data format');
                }
                Log::debug('Parsed cart items from JSON string', ['cartItems' => $cartItems]);
            }

            if (empty($cartItems) || !is_array($cartItems)) {
                throw new \Exception('No items in cart');
            }
            
            // Debug request data
            Log::debug('Order request data:', [
                'student_id' => $request->student_id,
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_type,
                'items' => $cartItems
            ]);

            // If payment method is deposit, check student balance
            if ($request->payment_type === 'deposit' && $request->student_id) {
                $currentBalance = CashDeposit::where('student_id', $request->student_id)
                    ->whereNull('deleted_at')
                    ->sum(DB::raw('Amount * CASE WHEN TransactionType = "DEPOSIT" THEN 1 ELSE -1 END'));

                if ($currentBalance < $request->total_amount) {
                    throw new \Exception('Insufficient deposit balance. Current balance: ' . number_format($currentBalance, 2));
                }
            }
            
            // Check stock availability for all items before proceeding
            foreach ($cartItems as $item) {
                if (!isset($item['id']) || !is_numeric($item['id'])) {
                    throw new \Exception('Invalid item ID');
                }

                if (!isset($item['quantity']) || !is_numeric($item['quantity'])) {
                    throw new \Exception('Invalid quantity');
                }

                if (empty($item['isCustom']) || !$item['isCustom']) {
                    $menuItem = MenuItem::findOrFail((int)$item['id']);
                    if (!$menuItem->hasSufficientStock($item['quantity'])) {
                        throw new \Exception("Insufficient stock for item: {$menuItem->ItemName}");
                    }
                }
            }
            
            // Generate order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(
                POSOrder::whereDate('created_at', today())->count() + 1, 
                4, 
                '0', 
                STR_PAD_LEFT
            );
            
            Log::debug('Generated order number: ' . $orderNumber);
            
            // Create the order
            $order = POSOrder::create([
                'student_id' => $request->student_id,
                'TotalAmount' => $request->total_amount,
                'PaymentMethod' => $request->payment_type,
                'Status' => $request->payment_type === 'deposit' ? 'completed' : 'pending',
                'OrderNumber' => $orderNumber,
                'ProcessedBy' => $request->payment_type === 'deposit' ? Auth::id() : null,
                'ProcessedAt' => $request->payment_type === 'deposit' ? now() : null,
                'AmountTendered' => $request->payment_type === 'cash' ? $request->amount_tendered : null,
                'ChangeAmount' => $request->payment_type === 'cash' ? $request->change_amount : null
            ]);
            
            Log::debug('Order created:', ['order_id' => $order->OrderID, 'order_data' => $order->toArray()]);
            
            // Add order items and update stock
            foreach ($cartItems as $item) {
                if (!empty($item['isCustom']) && $item['isCustom']) {
                    Log::debug('Processing custom item:', ['item' => $item]);
                    
                    // Insert custom item using DB query to bypass foreign key constraint
                    $orderItemId = DB::table('pos_order_items')->insertGetId([
                        'OrderID' => $order->OrderID,
                        'ItemID' => null, // Set ItemID to null for custom items
                        'Quantity' => $item['quantity'],
                        'UnitPrice' => $item['price'],
                        'Subtotal' => $item['price'] * $item['quantity'],
                        'ItemName' => $item['name'],
                        'IsCustomItem' => true,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    Log::debug('Custom order item created:', ['order_item_id' => $orderItemId]);
                } else {
                    // Regular menu item
                    if (!empty($item['id'])) {
                        $menuItem = MenuItem::findOrFail($item['id']);
                        Log::debug('Processing menu item:', ['menu_item' => $menuItem->toArray(), 'quantity' => $item['quantity']]);
                        
                        $orderItem = POSOrderItem::create([
                            'OrderID' => $order->OrderID,
                            'ItemID' => $menuItem->MenuItemID,
                            'Quantity' => $item['quantity'],
                            'UnitPrice' => $menuItem->Price,
                            'Subtotal' => $menuItem->Price * $item['quantity'],
                            'ItemName' => $menuItem->ItemName,
                            'IsCustomItem' => false
                        ]);
                        
                        // Decrement stock
                        $menuItem->decrementStock($item['quantity']);
                        
                        Log::debug('Order item created and stock updated:', [
                            'order_item' => $orderItem->toArray(),
                            'remaining_stock' => $menuItem->StocksAvailable
                        ]);
                    }
                }
            }

            // If payment method is deposit, deduct from student's balance
            if ($request->payment_type === 'deposit' && $request->student_id) {
                // Create a withdrawal transaction
                CashDeposit::create([
                    'student_id' => $request->student_id,
                    'TransactionDate' => now(),
                    'ReferenceNumber' => $orderNumber,
                    'TransactionType' => 'WITHDRAWAL',
                    'Amount' => $request->total_amount,
                    'BalanceBefore' => $currentBalance,
                    'BalanceAfter' => $currentBalance - $request->total_amount,
                    'Notes' => "Payment for Order #{$orderNumber}",
                    'Status' => 'completed'
                ]);

                Log::debug('Deposit withdrawal created for order payment', [
                    'student_id' => $request->student_id,
                    'amount' => $request->total_amount,
                    'balance_before' => $currentBalance,
                    'balance_after' => $currentBalance - $request->total_amount
                ]);
            }
            
            DB::commit();
            Log::debug('Order transaction committed successfully');
            
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully!',
                'order_number' => $orderNumber,
                'order_id' => $order->OrderID,
                'alert' => [
                    'type' => 'success',
                    'title' => 'Order Created!',
                    'text' => "Order #{$orderNumber} has been created successfully."
                ]
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating order: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            $responseData = [
                'success' => false,
                'message' => 'Error creating order: ' . $e->getMessage(),
                'alert' => [
                    'type' => 'error',
                    'title' => 'Order Failed',
                    'text' => 'Error: ' . $e->getMessage()
                ]
            ];
            
            return response()->json($responseData, 500);
        }
    }
    
    public function show(POSOrder $order)
    {
        $order->load(['items.item', 'student']);
        return view('pos.show', compact('order'));
    }
    
    public function process(POSOrder $order)
    {
        try {
            DB::beginTransaction();

            $order->Status = 'completed';
            $order->ProcessedBy = Auth::id();
            $order->ProcessedAt = now();
            $order->save();

            DB::commit();

            return redirect()->route('pos.show', $order->OrderID)
                ->with('success', 'Order processed successfully');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
    
    public function cancel(POSOrder $order)
    {
        try {
            DB::beginTransaction();

            // If it was a deposit payment, refund the amount
            if ($order->PaymentMethod === 'deposit' && $order->Status === 'completed') {
                $balance = CashDeposit::where('student_id', $order->student_id)
                    ->whereNull('deleted_at')
                    ->sum('Amount');

                CashDeposit::create([
                    'student_id' => $order->student_id,
                    'TransactionDate' => now(),
                    'ReferenceNumber' => "REFUND-{$order->OrderNumber}",
                    'TransactionType' => 'refund',
                    'Amount' => $order->TotalAmount,
                    'BalanceBefore' => $balance,
                    'BalanceAfter' => $balance + $order->TotalAmount,
                    'Notes' => "Refund for cancelled Order #{$order->OrderNumber}"
                ]);
            }

            $order->Status = 'cancelled';
            $order->save();

            DB::commit();

            return redirect()->route('pos.show', $order->OrderID)
                ->with('success', 'Order cancelled successfully');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
    
    public function getStudentBalance($studentId)
    {
        $balance = CashDeposit::where('student_id', $studentId)
            ->whereNull('deleted_at')
            ->sum(DB::raw('Amount * CASE WHEN TransactionType = "DEPOSIT" THEN 1 ELSE -1 END'));

        return response()->json(['balance' => $balance]);
    }
    
    public function filterByCategory($categoryId)
    {
        $menuItems = DB::table('menu_items')
                        ->whereNull('deleted_at')
                        ->where('IsAvailable', 1);
                        
        if ($categoryId) {
            $menuItems = $menuItems->where('ClassificationID', $categoryId);
        }
        
        $menuItems = $menuItems->get();
        
        return response()->json($menuItems);
    }
    
    // Cashiering function - for processing payments
    public function cashiering()
    {
        $pendingOrders = DB::table('pos_orders')
                           ->whereNull('deleted_at')
                           ->where('Status', 'pending')
                           ->orderBy('created_at', 'desc')
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
    
    // For payment transactions
    public function processPayment($orderId, Request $request)
    {
        try {
            DB::beginTransaction();
            
            // Validate inputs
            $validated = $request->validate([
                'payment_amount' => 'required|numeric|min:0',
                'payment_type' => 'required|in:cash,deposit'
            ]);
            
            // Get the order
            $order = DB::table('pos_orders')
                ->where('OrderID', $orderId)
                ->where('Status', 'pending') // Only pending orders can be processed
                ->whereNull('deleted_at')
                ->first();
                
            if (!$order) {
                return redirect()->back()->with('error', 'Order not found or cannot be processed');
            }
            
            // Verify payment amount
            $paymentAmount = $validated['payment_amount'];
            if ($paymentAmount < $order->TotalAmount) {
                return redirect()->back()->with('error', 'Payment amount cannot be less than the order total');
            }
            
            // Calculate change
            $changeAmount = $paymentAmount - $order->TotalAmount;
            
            // Process payment based on type
            if ($validated['payment_type'] === 'deposit') {
                // Check student deposit balance
                if (!$order->student_id) {
                    return redirect()->back()->with('error', 'Cannot use deposit payment for an order without a student');
                }
                
                $currentBalance = CashDeposit::where('student_id', $order->student_id)
                    ->whereNull('deleted_at')
                    ->sum(DB::raw('Amount * CASE WHEN TransactionType = "DEPOSIT" THEN 1 ELSE -1 END'));
                
                if ($currentBalance < $order->TotalAmount) {
                    return redirect()->back()->with('error', 'Insufficient deposit balance. Current balance: ₱' . number_format($currentBalance, 2));
                }
                
                // Create withdrawal transaction
                CashDeposit::create([
                    'student_id' => $order->student_id,
                    'TransactionDate' => now(),
                    'ReferenceNumber' => $order->OrderNumber,
                    'TransactionType' => 'WITHDRAWAL',
                    'Amount' => $order->TotalAmount,
                    'BalanceBefore' => $currentBalance,
                    'BalanceAfter' => $currentBalance - $order->TotalAmount,
                    'Notes' => "Payment for Order #{$order->OrderNumber}",
                    'Status' => 'completed'
                ]);

                Log::debug('Deposit withdrawal created for order payment', [
                    'student_id' => $order->student_id,
                    'amount' => $order->TotalAmount,
                    'balance_before' => $currentBalance,
                    'balance_after' => $currentBalance - $order->TotalAmount
                ]);
            }
            
            // Update order status to completed
            DB::table('pos_orders')
                ->where('OrderID', $orderId)
                ->update([
                    'Status' => 'completed',
                    'ProcessedBy' => Auth::id(),
                    'ProcessedAt' => now(),
                    'AmountTendered' => $validated['payment_type'] === 'cash' ? $paymentAmount : null,
                    'ChangeAmount' => $validated['payment_type'] === 'cash' ? $changeAmount : null,
                    'updated_at' => now()
                ]);
                
            DB::commit();
            
            return redirect()->route('pos.cashiering')
                ->with('success', 'Payment processed successfully. ' . 
                    ($validated['payment_type'] === 'deposit' ? 'Amount deducted from student deposit.' : 
                    'Change amount: ₱' . number_format($changeAmount, 2)));
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment processing failed:', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    // Add a menu item quickly
    public function addMenuItem(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'classification_id' => 'required|exists:classification,ClassificationId',
            'stocks_available' => 'required|integer|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            
            // Create menu item - ensure the field names exactly match the database table
            $menuItemId = DB::table('menu_items')->insertGetId([
                'ItemName' => $request->item_name,
                'Description' => $request->description,
                'Price' => $request->price,
                'ClassificationID' => $request->classification_id, // Note the exact case match to the database field
                'IsAvailable' => true,
                'IsDeleted' => false,
                'StocksAvailable' => $request->stocks_available,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            DB::commit();
            
            $successMessage = "Menu item '{$request->item_name}' added successfully!";
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'item' => [
                        'id' => $menuItemId,
                        'name' => $request->item_name,
                        'price' => $request->price,
                        'description' => $request->description,
                        'stocks' => $request->stocks_available
                    ]
                ]);
            }
            
            return redirect()->route('pos.create')
                ->with('success', $successMessage)
                ->with('alert', [
                    'type' => 'success',
                    'title' => 'Menu Item Added',
                    'text' => $successMessage
                ]);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            $errorMessage = 'Failed to add menu item: ' . $e->getMessage();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => $e instanceof \Illuminate\Validation\ValidationException ? $e->errors() : null
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', $errorMessage)
                ->with('alert', [
                    'type' => 'error',
                    'title' => 'Error Adding Menu Item',
                    'text' => $errorMessage
                ])
                ->withInput();
        }
    }
    
    // Cancel an order
    public function cancelOrder($orderId)
    {
        try {
            DB::beginTransaction();
            
            // Get the order
            $order = DB::table('pos_orders')
                ->where('OrderID', $orderId)
                ->where('Status', 'pending') // Only pending orders can be cancelled
                ->whereNull('deleted_at')
                ->first();
                
            if (!$order) {
                return redirect()->back()->with('error', 'Order not found or cannot be cancelled');
            }
            
            // Update order status
            DB::table('pos_orders')
                ->where('OrderID', $orderId)
                ->update([
                    'Status' => 'cancelled',
                    'updated_at' => now()
                ]);
                
            // Restore stock for each order item
            $orderItems = DB::table('pos_order_items')
                ->where('OrderID', $orderId)
                ->get();
                
            foreach ($orderItems as $item) {
                if (!empty($item->ItemID) && !$item->IsCustomItem) {
                    // Only increment stock for regular menu items
                    $menuItem = MenuItem::find($item->ItemID);
                    if ($menuItem) {
                        $menuItem->incrementStock($item->Quantity);
                        Log::debug('Stock restored for item:', [
                            'item_id' => $item->ItemID,
                            'quantity_restored' => $item->Quantity,
                            'new_stock' => $menuItem->StocksAvailable
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('pos.index')->with('success', 'Order has been cancelled successfully');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error cancelling order:', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }
    
    // Get order details (for AJAX)
    public function getOrderDetails($orderId)
    {
        try {
            $order = DB::table('pos_orders')
                ->leftJoin('students', 'pos_orders.student_id', '=', 'students.student_id')
                ->where('pos_orders.OrderID', $orderId)
                ->whereNull('pos_orders.deleted_at')
                ->select(
                    'pos_orders.*',
                    'students.first_name',
                    'students.last_name',
                    'students.student_id'
                )
                ->first();
                
            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }
            
            // Get order items - using pos_order_items table directly since it has all the info we need
            $items = DB::table('pos_order_items')
                ->where('OrderID', $orderId)
                ->select(
                    'OrderItemID',
                    'ItemID',
                    'ItemName',
                    'Quantity',
                    'UnitPrice',
                    'Subtotal',
                    'IsCustomItem'
                )
                ->get();

            // Format the response
            $response = [
                'order' => [
                    'OrderID' => $order->OrderID,
                    'OrderNumber' => $order->OrderNumber,
                    'Status' => $order->Status ?? 'Unknown',
                    'TotalAmount' => $order->TotalAmount ?? 0,
                    'PaymentMethod' => $order->PaymentMethod ?? 'Unknown',
                    'Notes' => $order->Notes ?? '',
                    'created_at' => $order->created_at,
                    'student' => $order->student_id ? [
                        'id' => $order->student_id,
                        'name' => trim($order->first_name . ' ' . $order->last_name)
                    ] : null
                ],
                'items' => $items->map(function($item) {
                    return [
                        'ItemID' => $item->ItemID,
                        'ItemName' => $item->ItemName ?? 'Unknown Item',
                        'Description' => '',
                        'Quantity' => $item->Quantity ?? 0,
                        'UnitPrice' => $item->UnitPrice ?? 0,
                        'Subtotal' => $item->Subtotal ?? 0,
                        'IsCustomItem' => $item->IsCustomItem ?? false
                    ];
                })
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Failed to load order details:', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to load order details: ' . $e->getMessage()], 500);
        }
    }

    // Search students for Select2 dropdown
    public function searchStudents(Request $request)
    {
        $term = $request->get('term');
        
        $students = DB::table('students')
            ->where('status', 'Active')
            ->where(function($query) use ($term) {
                $query->where('student_id', 'like', "%{$term}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$term}%");
            })
            ->select(
                'student_id as id',
                DB::raw("CONCAT(student_id, ' - ', first_name, ' ', last_name) as text")
            )
            ->limit(10)
            ->get();

        return response()->json([
            'results' => $students,
            'pagination' => ['more' => false]
        ]);
    }
} 