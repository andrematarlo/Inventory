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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Models\UnitOfMeasure;
use App\Models\User;

class POSController extends Controller
{
    public function index(Request $request)
    {
        // For deposits view
        if ($request->route()->getName() === 'pos.deposits.index') {
            $today = now()->startOfDay();
            
            // Today's deposits (no status check since there's no Status column)
            $todayDeposits = CashDeposit::where('TransactionDate', '>=', $today)
                ->where('TransactionType', 'DEPOSIT')
                ->whereNull('deleted_at')
                ->sum('Amount');
            
            $todayDepositCount = CashDeposit::where('TransactionDate', '>=', $today)
                ->where('TransactionType', 'DEPOSIT')
                ->whereNull('deleted_at')
                ->count();
            
            // All active deposits (assuming all non-deleted deposits are active)
            $activeDeposits = CashDeposit::where('TransactionType', 'DEPOSIT')
                ->whereNull('deleted_at')
                ->sum('Amount');
            
            $activeDepositCount = CashDeposit::where('TransactionType', 'DEPOSIT')
                ->whereNull('deleted_at')
                ->count();
            
            // Since there's no Status column, we'll set these to 0
            $pendingDeposits = 0;
            $pendingDepositCount = 0;
            
            // Get all deposits with student information
            $deposits = CashDeposit::with('student')
                ->whereNull('deleted_at')
                ->latest('TransactionDate')
                ->paginate(15);
            
            return view('pos.deposits.index', compact(
                'deposits',
                'todayDeposits',
                'todayDepositCount',
                'activeDeposits',
                'activeDepositCount',
                'pendingDeposits',
                'pendingDepositCount'
            ));
        }

        // For orders view (existing functionality)
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
        
        // Get classifications for menu item modal
        $classifications = Classification::where('IsDeleted', 0)->orderBy('ClassificationName')->get();

        return view('pos.orders.index', compact('orders', 'isStudent', 'studentBalance', 'studentId', 'classifications'));
    }
    
    public function create()
    {
        $menuItems = MenuItem::active()
            ->with('classification')
            ->get();
                        
        $classifications = Classification::where('IsDeleted', false)
                      ->orderBy('ClassificationName')
                      ->get();
        
        return view('pos.create', compact('menuItems', 'classifications'));
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

            // If payment method is deposit, check student balance and limit
            if ($request->payment_type === 'deposit' && $request->student_id) {
                $currentBalance = CashDeposit::where('student_id', $request->student_id)
                    ->whereNull('deleted_at')
                    ->sum(DB::raw('CASE WHEN TransactionType = "DEPOSIT" THEN Amount WHEN TransactionType = "PURCHASE" THEN -Amount ELSE 0 END'));

                $newBalance = $currentBalance - $request->total_amount;
                
                // Get student's negative balance limit from database
                $limit = DB::table('student_deposits')
                    ->where('student_id', $request->student_id)
                    ->value('limit') ?? 0;
                
                // Since limit is stored as negative in database, compare directly
                if ($newBalance < $limit) {
                    throw new \Exception("This transaction would exceed the student's negative balance limit. Maximum allowed negative balance is ₱{$limit}. Current balance: ₱{$currentBalance}");
                }

                // Record the transaction if within limit
                Log::debug('Processing deposit payment', [
                    'student_id' => $request->student_id,
                    'current_balance' => $currentBalance,
                    'order_amount' => $request->total_amount,
                    'new_balance' => $newBalance,
                    'limit' => $limit
                ]);
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
                    
                    // Check stock for the menu item itself
                    if (!$menuItem->hasSufficientStock($item['quantity'])) {
                        throw new \Exception("Insufficient stock for item: {$menuItem->ItemName}");
                    }

                    // If it's a value meal, also check stock of included items
                    if ($menuItem->IsValueMeal) {
                        $valueMealItems = $menuItem->valueMealItems()->with('menuItem')->get();
                        foreach ($valueMealItems as $valueMealItem) {
                            $requiredQuantity = $valueMealItem->quantity * $item['quantity'];
                            if ($valueMealItem->menuItem->StocksAvailable < $requiredQuantity) {
                                throw new \Exception("Insufficient stock for included item: {$valueMealItem->menuItem->ItemName} in value meal {$menuItem->ItemName}");
                            }
                        }
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
                'Status' => 'pending',
                'OrderNumber' => $orderNumber,
                'ProcessedBy' => Auth::id(),
                'ProcessedAt' => now(),
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
                        
                        // Create order item
                        $orderItem = POSOrderItem::create([
                            'OrderID' => $order->OrderID,
                            'ItemID' => $menuItem->MenuItemID,
                            'Quantity' => $item['quantity'],
                            'UnitPrice' => $menuItem->Price,
                            'Subtotal' => $menuItem->Price * $item['quantity'],
                            'ItemName' => $menuItem->ItemName,
                            'IsCustomItem' => false
                        ]);
                        
                        // Deduct stock from the menu item itself
                        $menuItem->StocksAvailable -= $item['quantity'];
                        $menuItem->save();
                        
                        Log::debug('Stock updated for menu item:', [
                            'item_id' => $menuItem->MenuItemID,
                            'item_name' => $menuItem->ItemName,
                            'previous_stock' => $menuItem->StocksAvailable + $item['quantity'],
                            'quantity_ordered' => $item['quantity'],
                            'new_stock' => $menuItem->StocksAvailable
                        ]);

                        // If it's a value meal, also deduct stock from included items
                        if ($menuItem->IsValueMeal) {
                            $valueMealItems = $menuItem->valueMealItems()->with('menuItem')->get();
                            foreach ($valueMealItems as $valueMealItem) {
                                $deductQuantity = $valueMealItem->quantity * $item['quantity'];
                                $includedItem = $valueMealItem->menuItem;
                                $includedItem->StocksAvailable -= $deductQuantity;
                                $includedItem->save();
                                
                                Log::debug('Updated included item stock:', [
                                    'value_meal' => $menuItem->ItemName,
                                    'included_item' => $includedItem->ItemName,
                                    'previous_stock' => $includedItem->StocksAvailable + $deductQuantity,
                                    'deducted_quantity' => $deductQuantity,
                                    'new_stock' => $includedItem->StocksAvailable
                                ]);
                            }
                        }
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
                    'TransactionType' => 'PURCHASE',
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
                    'text' => "Order #{$orderNumber} has been created successfully.",
                    'footer' => 'Stock levels have been updated automatically.'
                ],
                'updated_stocks' => collect($cartItems)->map(function($item) {
                    if (!empty($item['id']) && empty($item['isCustom'])) {
                        $menuItem = MenuItem::find($item['id']);
                        $stockUpdates = [
                            [
                            'item_name' => $menuItem->ItemName,
                            'new_stock' => $menuItem->StocksAvailable
                            ]
                        ];

                        if ($menuItem->IsValueMeal) {
                            // Add stock updates for included items
                            $includedItemsStocks = $menuItem->valueMealItems()->with('menuItem')->get()->map(function($valueMealItem) {
                                return [
                                    'item_name' => $valueMealItem->menuItem->ItemName,
                                    'new_stock' => $valueMealItem->menuItem->StocksAvailable
                                ];
                            })->toArray();
                            
                            return array_merge($stockUpdates, $includedItemsStocks);
                        }

                        return $stockUpdates;
                    }
                })->filter()->flatten(1)
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
            // Debug log
            Log::debug('Processing order:', [
                'order_id' => $order->OrderID,
                'order_number' => $order->OrderNumber,
                'current_status' => $order->Status
            ]);
            
            // Validate that we have the required fields
            if (!$order->OrderNumber) {
                throw new \Exception('Order is missing an order number and cannot be processed');
            }
            
            if ($order->Status !== 'pending') {
                throw new \Exception('Only pending orders can be processed.');
            }
            
            DB::beginTransaction();

            // No need to create a new record, just update the existing one
            $order->Status = 'completed';
            $order->ProcessedBy = Auth::id();
            $order->ProcessedAt = now();
            $order->save();

            DB::commit();
            
            Log::debug('Order processed successfully', [
                'order_id' => $order->OrderID,
                'order_number' => $order->OrderNumber,
                'new_status' => $order->Status
            ]);

            return redirect()->route('pos.show', $order->OrderID)
                ->with('success', 'Order processed successfully');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Order processing failed:', [
                'order_id' => $order->OrderID ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
            ->sum(DB::raw('CASE WHEN TransactionType = "DEPOSIT" THEN Amount WHEN TransactionType = "PURCHASE" THEN -Amount ELSE 0 END'));

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
        $validated = $request->validate([
            'student_id' => 'required|string|exists:students,student_id',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            
            // Check if student exists
            $student = DB::table('students')
                ->where('student_id', $validated['student_id'])
                ->first();
                
            if (!$student) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Student not found with ID: ' . $validated['student_id']
                    ], 404);
                }
                
                return redirect()->back()->with('error', 'Student not found with ID: ' . $validated['student_id']);
            }
            
            // Get current balance
            $currentBalance = CashDeposit::where('student_id', $validated['student_id'])
                ->whereNull('deleted_at')
                ->sum(DB::raw('CASE WHEN TransactionType = "DEPOSIT" THEN Amount WHEN TransactionType = "PURCHASE" THEN -Amount ELSE 0 END'));
                
            // Create deposit record
            $deposit = CashDeposit::create([
                'student_id' => $validated['student_id'],
                'TransactionDate' => now(),
                'ReferenceNumber' => 'DEP-' . time(),
                'TransactionType' => 'DEPOSIT',
                'Amount' => $validated['amount'],
                'BalanceBefore' => $currentBalance,
                'BalanceAfter' => $currentBalance + $validated['amount'],
                'Notes' => $validated['notes'],
            ]);
            
            DB::commit();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Deposit of ₱' . number_format($validated['amount'], 2) . ' added successfully',
                    'deposit' => $deposit
                ]);
            }
            
            return redirect()->route('pos.deposits.index')
                ->with('success', 'Deposit of ₱' . number_format($validated['amount'], 2) . ' was successfully added for student ' . $student->first_name . ' ' . $student->last_name);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process deposit: ' . $e->getMessage()
                ], 500);
            }
            
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
    public function processPayment($id)
    {
        try {
            $order = Order::with(['items', 'customer'])->findOrFail($id);
            
            if ($order->Status === 'completed') {
                return redirect()->route('pos.orders.index')
                    ->with('error', 'Order has already been processed.');
            }
            
            return view('pos.processpayment', compact('order'));
        } catch (\Exception $e) {
            return redirect()->route('pos.orders.index')
                ->with('error', 'Error loading order: ' . $e->getMessage());
        }
    }

    public function postProcessPayment(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($id);
            
            if ($order->Status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order has already been processed'
                ]);
            }

            $validated = $request->validate([
                'payment_type' => 'required|in:cash,deposit',
                'amount_tendered' => 'required_if:payment_type,cash|numeric|min:' . $order->TotalAmount,
            ]);

            // Get current balance if using deposit
            if ($validated['payment_type'] === 'deposit' && $order->student_id) {
                $currentBalance = CashDeposit::where('student_id', $order->student_id)
                    ->whereNull('deleted_at')
                    ->sum(DB::raw('CASE WHEN TransactionType = "DEPOSIT" THEN Amount WHEN TransactionType = "PURCHASE" THEN -Amount ELSE 0 END'));

                if ($currentBalance < $order->TotalAmount) {
                    throw new \Exception('Insufficient student deposit balance');
                }
            }

            $order->PaymentMethod = $validated['payment_type'];
            $order->Status = 'completed';
            $order->ProcessedAt = now();

            if ($validated['payment_type'] === 'cash') {
                $order->AmountTendered = $validated['amount_tendered'];
                $order->ChangeAmount = $validated['amount_tendered'] - $order->TotalAmount;
            } elseif ($validated['payment_type'] === 'deposit' && $order->student_id) {
                // Create withdrawal transaction
                CashDeposit::create([
                    'student_id' => $order->student_id,
                    'TransactionDate' => now(),
                    'ReferenceNumber' => "PAY-{$order->OrderNumber}",
                    'TransactionType' => 'PURCHASE',
                    'Amount' => $order->TotalAmount,
                    'BalanceBefore' => $currentBalance,
                    'BalanceAfter' => $currentBalance - $order->TotalAmount,
                    'Notes' => "Payment for Order #{$order->OrderNumber}"
                ]);
            }

            $order->save();

            // Update stock levels
            foreach ($order->items as $item) {
                $menuItem = MenuItem::find($item->ItemID);
                if ($menuItem) {
                    $menuItem->StocksAvailable -= $item->Quantity;
                    $menuItem->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'order_number' => $order->OrderNumber,
                'order_id' => $order->OrderID
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
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
            'item_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            DB::beginTransaction();
            
            // Handle image upload if present
            $imagePath = null;
            if ($request->hasFile('item_image')) {
                $image = $request->file('item_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('menu-items', $imageName, 'public');
            }
            
            // Create menu item
            $menuItemId = DB::table('menu_items')->insertGetId([
                'ItemName' => $request->item_name,
                'Description' => $request->description,
                'Price' => $request->price,
                'ClassificationID' => $request->classification_id,
                'IsAvailable' => true,
                'IsDeleted' => false,
                'StocksAvailable' => $request->stocks_available,
                'image_path' => $imagePath, // Save the image path
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
                        'stocks' => $request->stocks_available,
                        'image_path' => $imagePath ? asset('storage/' . $imagePath) : null
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
            
            // If image was uploaded but transaction failed, remove the image
            if (isset($imagePath) && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            
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
    public function cancelOrder($id)
    {
        try {
            DB::beginTransaction();
            
            $order = POSOrder::findOrFail($id);
            
            if ($order->Status === 'completed' || $order->Status === 'cancelled') {
                return redirect()->back()->with('error', 'This order is already ' . $order->Status);
            }
            
            // Update order status
            $order->Status = 'cancelled';
            $order->save();
            
            // Return stock for menu items
            $orderItems = POSOrderItem::where('OrderID', $order->OrderID)->get();
            
            foreach ($orderItems as $item) {
                if (!empty($item->ItemID) && !$item->IsCustomItem) {
                    // Only increment stock for regular menu items
                    $menuItem = MenuItem::find($item->ItemID);
                    if ($menuItem) {
                        $menuItem->StocksAvailable += $item->Quantity;
                        $menuItem->save();
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('pos.index')->with('success', 'Order has been cancelled successfully');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error cancelling order:', [
                'order_id' => $id,
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
        $term = $request->input('term');
        
        $students = DB::table('students')
            ->where('status', 'Active')
            ->where(function($query) use ($term) {
                $query->where('student_id', 'like', "%{$term}%")
                      ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$term}%");
            })
            ->select('student_id', 'first_name', 'last_name')
            ->limit(10)
            ->get();

        return response()->json([
            'students' => $students
        ]);
    }

    public function deleteMenuItem($id)
    {
        try {
            DB::beginTransaction();

            // Find the menu item
            $menuItem = MenuItem::where('MenuItemID', $id)->firstOrFail();
            
            // Delete the image if it exists
            if ($menuItem->image_path && Storage::disk('public')->exists($menuItem->image_path)) {
                Storage::disk('public')->delete($menuItem->image_path);
            }

            // Soft delete the menu item
            $menuItem->IsDeleted = true;
            $menuItem->save();

            DB::commit();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Menu item deleted successfully'
                ]);
            }

            return redirect()->route('pos.menu-items.index')
                ->with('success', 'Menu item deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete menu item: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to delete menu item: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of menu items
     */
    public function menuItems()
    {
        try {
            // Get all menu items including deleted ones
            $menuItems = MenuItem::with(['classification', 'unit'])
                ->orderBy('ItemName')
                ->get();

            // Get all classifications for the filter dropdown
            $categories = Classification::orderBy('ClassificationName')->get();

            // Get all units for the dropdown
            $units = UnitOfMeasure::where('IsDeleted', 0)
                ->orderBy('UnitName')
                ->get();

            return view('pos.menu-items.index', compact('menuItems', 'categories', 'units'));

        } catch (\Exception $e) {
            Log::error('Error loading menu items: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to load menu items: ' . $e->getMessage());
        }
    }
    
    /**
     * Show the form for creating a new menu item
     */
    public function createMenuItem()
    {
        $classifications = Classification::where('IsDeleted', false)
                      ->orderBy('ClassificationName')
                      ->get();
                      
        return view('pos.menu-items.create', compact('classifications'));
    }
    
    /**
     * Store a newly created menu item
     */
    public function storeMenuItem(Request $request)
    {
        try {
            Log::info('Creating menu item:', ['request_data' => $request->all()]);

            $validator = Validator::make($request->all(), [
                'ItemName' => 'required|string|max:255',
                'Price' => 'required|numeric|min:0',
                'ClassificationID' => 'required|exists:classification,ClassificationId',
                'Description' => 'nullable|string',
                'StocksAvailable' => 'required_if:IsValueMeal,0|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'UnitOfMeasureID' => 'required|exists:unitofmeasure,UnitOfMeasureId',
                'IsValueMeal' => 'boolean',
                'value_meal_items' => 'required_if:IsValueMeal,1|array|min:1',
                'value_meal_items.*.menu_item_id' => 'required_with:value_meal_items|exists:menu_items,MenuItemID',
                'value_meal_items.*.quantity' => 'required_with:value_meal_items|integer|min:1'
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed:', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $menuItem = new MenuItem();
            $menuItem->ItemName = $request->ItemName;
            $menuItem->Price = $request->Price;
            $menuItem->ClassificationID = $request->ClassificationID;
            $menuItem->Description = $request->Description;
            $menuItem->UnitOfMeasureID = $request->UnitOfMeasureID;
            $menuItem->IsValueMeal = $request->IsValueMeal ?? false;
            
            // Handle stocks for value meals vs regular items
            if (!$menuItem->IsValueMeal) {
            $menuItem->StocksAvailable = $request->StocksAvailable;
            } else {
                // For value meals, calculate the maximum possible meals based on included items
                $maxMeals = PHP_INT_MAX;
                foreach ($request->value_meal_items as $item) {
                    $includedItem = MenuItem::findOrFail($item['menu_item_id']);
                    if ($includedItem->IsValueMeal) {
                        throw new \Exception("Cannot include a value meal inside another value meal: {$includedItem->ItemName}");
                    }
                    $possibleMeals = floor($includedItem->StocksAvailable / $item['quantity']);
                    $maxMeals = min($maxMeals, $possibleMeals);
                }
                $menuItem->StocksAvailable = $maxMeals;
            }
            
            $menuItem->IsAvailable = true;
            $menuItem->IsDeleted = false;
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('menu-items', 'public');
                $menuItem->image_path = $imagePath;
            }

            $menuItem->save();

            // If this is a value meal, create the value meal items
            if ($menuItem->IsValueMeal && $request->has('value_meal_items')) {
                foreach ($request->value_meal_items as $item) {
                    $includedItem = MenuItem::findOrFail($item['menu_item_id']);
                    if ($includedItem->IsValueMeal) {
                        throw new \Exception("Cannot include a value meal inside another value meal: {$includedItem->ItemName}");
                    }
                    
                    $menuItem->valueMealItems()->create([
                        'menu_item_id' => $item['menu_item_id'],
                        'quantity' => $item['quantity']
                    ]);
                }
            }

            DB::commit();
            
            Log::info('Menu item created successfully:', ['menu_item' => $menuItem->toArray()]);

            return response()->json([
                'success' => true,
                'message' => 'Menu item created successfully',
                'data' => $menuItem
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating menu item:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create menu item: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Show the form for editing the specified menu item
     */
    public function editMenuItem($id)
    {
        $menuItem = MenuItem::where('MenuItemID', $id)
            ->where('IsDeleted', false)
            ->firstOrFail();
            
        $categories = DB::table('classification')
            ->where('IsDeleted', 0)  // Using 0 instead of false for bit field
            ->orderBy('ClassificationName')
            ->get();
            
        return view('pos.menu-items.edit', compact('menuItem', 'categories'));
    }
    
    /**
     * Update the specified menu item
     */
    public function updateMenuItem(Request $request, $id)
    {
        try {
            Log::info('Update menu item request:', [
                'id' => $id,
                'request_data' => $request->all()
            ]);

            $validator = Validator::make($request->all(), [
                'ItemName' => 'required|string|max:255',
                'Price' => 'required|numeric|min:0',
                'ClassificationID' => 'required|exists:classification,ClassificationId',
                'Description' => 'nullable|string',
                'StocksAvailable' => 'required_if:IsValueMeal,0|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'UnitOfMeasureID' => 'required|exists:unitofmeasure,UnitOfMeasureId',
                'IsValueMeal' => 'boolean',
                'value_meal_items' => 'required_if:IsValueMeal,1|array',
                'value_meal_items.*.menu_item_id' => 'required_if:IsValueMeal,1|exists:menu_items,MenuItemID',
                'value_meal_items.*.quantity' => 'required_if:IsValueMeal,1|integer|min:1'
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed:', [
                    'id' => $id,
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $menuItem = MenuItem::where('MenuItemID', $id)
                ->where('IsDeleted', false)
                ->firstOrFail();

            Log::info('Current menu item state:', [
                'id' => $id,
                'current_data' => $menuItem->toArray()
            ]);
                
            $menuItem->ItemName = $request->ItemName;
            $menuItem->Price = $request->Price;
            $menuItem->ClassificationID = $request->ClassificationID;
            $menuItem->Description = $request->Description;
            $menuItem->UnitOfMeasureID = $request->UnitOfMeasureID;
            $menuItem->IsValueMeal = $request->IsValueMeal ?? false;
            
            // Handle stocks for value meals vs regular items
            if (!$menuItem->IsValueMeal) {
            $menuItem->StocksAvailable = $request->StocksAvailable;
            } else {
                // For value meals, calculate the maximum possible meals based on included items
                $maxMeals = PHP_INT_MAX;
                foreach ($request->value_meal_items as $item) {
                    $includedItem = MenuItem::findOrFail($item['menu_item_id']);
                    if ($includedItem->IsValueMeal) {
                        throw new \Exception("Cannot include a value meal inside another value meal: {$includedItem->ItemName}");
                    }
                    $possibleMeals = floor($includedItem->StocksAvailable / $item['quantity']);
                    $maxMeals = min($maxMeals, $possibleMeals);
                }
                $menuItem->StocksAvailable = $maxMeals;
            }
            
            // Handle image upload
            if ($request->hasFile('image')) {
                Log::info('Processing image upload for menu item:', ['id' => $id]);
                // Delete old image if exists
                if ($menuItem->image_path && Storage::disk('public')->exists($menuItem->image_path)) {
                    Storage::disk('public')->delete($menuItem->image_path);
                }
                $imagePath = $request->file('image')->store('menu-items', 'public');
                $menuItem->image_path = $imagePath;
            }

            $menuItem->save();

            // If this is a value meal, update the value meal items
            if ($menuItem->IsValueMeal && $request->has('value_meal_items')) {
                // Delete existing value meal items
                $menuItem->valueMealItems()->delete();
                
                // Create new value meal items
                foreach ($request->value_meal_items as $item) {
                    $includedItem = MenuItem::findOrFail($item['menu_item_id']);
                    if ($includedItem->IsValueMeal) {
                        throw new \Exception("Cannot include a value meal inside another value meal: {$includedItem->ItemName}");
                    }
                    
                    $menuItem->valueMealItems()->create([
                        'menu_item_id' => $item['menu_item_id'],
                        'quantity' => $item['quantity']
                    ]);
                }
            }

            DB::commit();
            
            Log::info('Menu item updated successfully:', [
                'id' => $id,
                'updated_data' => $menuItem->toArray()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Menu item updated successfully',
                'data' => $menuItem
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating menu item:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update menu item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processById($id)
    {
        try {
            DB::beginTransaction();
            
            $order = POSOrder::findOrFail($id);
            
            if ($order->Status === 'ready' || $order->Status === 'cancelled') {
                return redirect()->back()->with('error', 'This order is already ' . $order->Status);
            }
            
            // Update order status based on current status
            if ($order->Status === 'paid') {
                $order->Status = 'preparing';
            } else if ($order->Status === 'preparing') {
                $order->Status = 'ready';
            }
            
            $order->ProcessedBy = Auth::id();
            $order->ProcessedAt = now();
            $order->save();
            
            DB::commit();
            
            return redirect()->route('pos.show', $order->OrderID)->with('success', 'Order status has been updated successfully');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error processing order by ID:', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to process order: ' . $e->getMessage());
        }
    }

    /**
     * Display the cashier interface
     */
    public function cashierIndex()
    {
        // Get the available menu items for the cashier interface
        $menuItems = MenuItem::where('IsDeleted', false)
            ->where('IsAvailable', true)
            ->orderBy('ItemName')
            ->get();
        
        // Get all available classifications/categories for filtering
        $categories = Classification::where('IsDeleted', false)
            ->orderBy('ClassificationName')
            ->get();
        
        // Default to empty cart
        $cartItems = [];
        
        // Check if user is a student
        $isStudent = Auth::check() && Auth::user()->role === 'Student';
        $studentBalance = 0;
        $studentId = null;
        
        if ($isStudent) {
            $studentId = Auth::user()->student_id;
            $studentBalance = CashDeposit::where('student_id', $studentId)
                ->whereNull('deleted_at')
                ->sum('Amount');
        }
        
        return view('pos.cashier', compact('menuItems', 'categories', 'cartItems', 'isStudent', 'studentBalance', 'studentId'));
    }

    public function checkStock($id)
    {
        try {
            $menuItem = MenuItem::findOrFail($id);
            return response()->json([
                'success' => true,
                'stock' => $menuItem->StocksAvailable
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking stock'
            ], 500);
        }
    }

    public function updateOrder(Request $request, $id)
    {
        try {
            Log::info('Updating order', ['id' => $id, 'request_data' => $request->all()]);
            
            $validator = Validator::make($request->all(), [
                'Status' => 'required|in:pending,paid,preparing,ready,completed,cancelled',
                'PaymentMethod' => 'required|in:cash,deposit',
                'AmountTendered' => 'nullable|numeric|min:0',
                'Remarks' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $order = POSOrder::findOrFail($id);
            Log::info('Found order', ['order' => $order->toArray()]);
            
            DB::beginTransaction();
            
            $order->Status = $request->Status;
            $order->save();
            
            DB::commit();
            Log::info('Order updated successfully', ['order_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully'
            ]);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Order not found', ['id' => $id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation error', ['id' => $id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating order', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the order'
            ], 500);
        }
    }

    public function checkStudentBalance($studentId)
    {
        try {
            $student = Student::findOrFail($studentId);

            // Get current balance
            $balance = DB::table('student_deposits')
                ->where('student_id', $studentId)
                ->whereNull('deleted_at')
                ->sum(DB::raw('Amount * CASE WHEN TransactionType = "DEPOSIT" THEN 1 WHEN TransactionType = "PURCHASE" THEN -1 ELSE 0 END'));

            // Get student's limit
            $limit = DB::table('student_deposits')
                ->where('student_id', $studentId)
                ->value('limit') ?? 0;

                return response()->json([
                'success' => true,
                'student' => [
                    'id' => $student->student_id,
                    'name' => $student->FirstName . ' ' . $student->LastName
                ],
                'balance' => $balance,
                'limit' => $limit,
                'available_credit' => $balance + $limit
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking student balance'
            ], 500);
        }
    }

    public function toggleMenuItemAvailability($id)
    {
        try {
            $menuItem = MenuItem::findOrFail($id);
            $menuItem->IsAvailable = !$menuItem->IsAvailable;
            $menuItem->save();

            return response()->json([
                'success' => true,
                'message' => 'Menu item availability updated successfully.',
                'is_available' => $menuItem->IsAvailable
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling menu item availability: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update menu item availability.'
            ], 500);
        }
    }

    public function restoreMenuItem($id)
    {
        try {
            DB::beginTransaction();

            // Find the menu item
            $menuItem = MenuItem::where('MenuItemID', $id)
                ->where('IsDeleted', true)
                ->firstOrFail();

            // Restore the menu item
            $menuItem->IsDeleted = false;
            $menuItem->save();

            DB::commit();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Menu item restored successfully'
                ]);
            }

            return redirect()->route('pos.menu-items.index')
                ->with('success', 'Menu item restored successfully');

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Menu item not found for restore:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu item not found'
                ], 404);
            }

            return redirect()->back()
                ->with('error', 'Menu item not found');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to restore menu item:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to restore menu item: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to restore menu item: ' . $e->getMessage());
        }
    }

    public function salesReport(Request $request)
    {
        $dateRange = $request->get('date_range', 'last7days');
        $startDate = null;
        $endDate = null;
        $itemId = $request->get('item_id');

        switch ($dateRange) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'yesterday':
                $startDate = Carbon::yesterday();
                $endDate = Carbon::yesterday()->endOfDay();
                break;
            case 'last7days':
                $startDate = Carbon::now()->subDays(6)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'last30days':
                $startDate = Carbon::now()->subDays(29)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'custom':
                $startDate = $request->get('date_from') ? Carbon::parse($request->get('date_from'))->startOfDay() : null;
                $endDate = $request->get('date_to') ? Carbon::parse($request->get('date_to'))->endOfDay() : null;
                break;
        }

        // Get all items for the dropdown
        $items = DB::table('menu_items')
            ->where('IsDeleted', 0)
            ->select(
                'MenuItemID as id',
                'ItemName as name'
            )
            ->orderBy('ItemName')
            ->get();

        // Get all cashiers for the dropdown
        $cashiers = DB::table('useraccount')
            ->join('employee', 'employee.UserAccountID', '=', 'useraccount.UserAccountID')
            ->where('useraccount.role', 'cashier')
            ->where('useraccount.IsDeleted', false)
            ->select(
                'useraccount.UserAccountID as id',
                DB::raw("CONCAT(employee.FirstName, ' ', employee.LastName) as name")
            )
            ->orderBy('employee.FirstName')
            ->orderBy('employee.LastName')
            ->get();

        // Base query for sales data
        $salesQuery = DB::table('pos_order_items')
            ->join('pos_orders', 'pos_order_items.OrderID', '=', 'pos_orders.OrderID')
            ->whereBetween('pos_orders.created_at', [$startDate, $endDate])
            ->where('pos_orders.Status', 'COMPLETED');

        if ($itemId) {
            $salesQuery->where('pos_order_items.ItemID', $itemId);
        }

        // For daily sales
        $dailySales = clone $salesQuery;
        $dailySales = $dailySales->select(
                DB::raw('DATE(pos_orders.created_at) as date'),
                DB::raw('SUM(pos_order_items.Subtotal) as total_sales')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // For weekly sales
        $weeklySales = clone $salesQuery;
        $weeklySales = $weeklySales->select(
                DB::raw('YEARWEEK(pos_orders.created_at) as week'),
                DB::raw('SUM(pos_order_items.Subtotal) as total_sales')
            )
            ->groupBy('week')
            ->orderBy('week')
            ->get();

        // For monthly sales
        $monthlySales = clone $salesQuery;
        $monthlySales = $monthlySales->select(
                DB::raw('DATE_FORMAT(pos_orders.created_at, "%Y-%m") as month'),
                DB::raw('SUM(pos_order_items.Subtotal) as total_sales')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // For yearly sales
        $yearlySales = clone $salesQuery;
        $yearlySales = $yearlySales->select(
                DB::raw('YEAR(pos_orders.created_at) as year'),
                DB::raw('SUM(pos_order_items.Subtotal) as total_sales')
            )
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        $chartData = [
            'daily' => [
                'labels' => $dailySales->pluck('date')->toArray(),
                'data' => $dailySales->pluck('total_sales')->toArray()
            ],
            'weekly' => [
                'labels' => $weeklySales->pluck('week')->toArray(),
                'data' => $weeklySales->pluck('total_sales')->toArray()
            ],
            'monthly' => [
                'labels' => $monthlySales->pluck('month')->toArray(),
                'data' => $monthlySales->pluck('total_sales')->toArray()
            ],
            'yearly' => [
                'labels' => $yearlySales->pluck('year')->toArray(),
                'data' => $yearlySales->pluck('total_sales')->toArray()
            ]
        ];

        // Calculate totals
        $totalsQuery = clone $salesQuery;
        $totals = $totalsQuery->select(
                DB::raw('COUNT(DISTINCT pos_orders.OrderID) as total_orders'),
                DB::raw('SUM(pos_order_items.Quantity) as total_items'),
                DB::raw('SUM(pos_order_items.Subtotal) as total_sales')
            )
            ->first();

        // Get top selling items
        $topItemsQuery = DB::table('pos_order_items')
            ->join('pos_orders', 'pos_order_items.OrderID', '=', 'pos_orders.OrderID')
            ->join('menu_items', 'pos_order_items.ItemID', '=', 'menu_items.MenuItemID')
            ->whereBetween('pos_orders.created_at', [$startDate, $endDate])
            ->where('pos_orders.Status', 'COMPLETED');
            
        // Apply item filter if provided
        if ($itemId) {
            $topItemsQuery->where('pos_order_items.ItemID', $itemId);
        }
        
        $topItems = $topItemsQuery->select(
                'menu_items.ItemName',
                'menu_items.image_path',
                DB::raw('SUM(pos_order_items.Quantity) as total_quantity'),
                DB::raw('SUM(pos_order_items.Subtotal) as total_revenue')
            )
            ->groupBy('menu_items.ItemName', 'menu_items.image_path')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // Get payment methods
        $paymentMethodsQuery = DB::table('pos_orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('Status', 'COMPLETED');
            
        // Apply item filter if provided
        if ($itemId) {
            $paymentMethodsQuery->whereExists(function ($query) use ($itemId) {
                $query->select(DB::raw(1))
                    ->from('pos_order_items')
                    ->whereRaw('pos_order_items.OrderID = pos_orders.OrderID')
                    ->where('pos_order_items.ItemID', $itemId);
            });
        }
        
        $paymentMethods = $paymentMethodsQuery->select(
                'PaymentMethod',
                DB::raw('SUM(TotalAmount) as total_amount')
            )
            ->groupBy('PaymentMethod')
            ->get();

        // Get sales data
        $salesQuery = DB::table('pos_order_items')
            ->join('pos_orders', 'pos_order_items.OrderID', '=', 'pos_orders.OrderID')
            ->whereBetween('pos_orders.created_at', [$startDate, $endDate])
            ->where('pos_orders.Status', 'COMPLETED')
            ->select(
                'pos_orders.OrderID',
                'pos_orders.created_at',
                'pos_order_items.ItemName',
                'pos_order_items.Quantity',
                'pos_order_items.UnitPrice',
                'pos_order_items.Subtotal',
                'pos_orders.student_id',
                'pos_orders.PaymentMethod',
                'pos_orders.Status'
            );
            
        // Apply item filter if provided
        if ($itemId) {
            $salesQuery->where('pos_order_items.ItemID', $itemId);
        }
        
        $sales = $salesQuery->orderByDesc('pos_orders.created_at')
            ->paginate(10);

        return view('pos.reports.sales', compact(
            'dateRange',
            'startDate',
            'endDate',
            'totals',
            'topItems',
            'paymentMethods',
            'sales',
            'chartData',
            'items',
            'cashiers',
            'itemId'
        ));
    }

    public function depositsReport(Request $request)
    {
        try {
            // Get date range from request or default to last 30 days
            $dateRange = $request->get('date_range', 'last30days');
            $startDate = null;
            $endDate = null;

            switch ($dateRange) {
                case 'today':
                    $startDate = now()->startOfDay();
                    $endDate = now()->endOfDay();
                    break;
                case 'yesterday':
                    $startDate = now()->subDay()->startOfDay();
                    $endDate = now()->subDay()->endOfDay();
                    break;
                case 'last7days':
                    $startDate = now()->subDays(7)->startOfDay();
                    $endDate = now()->endOfDay();
                    break;
                case 'last30days':
                    $startDate = now()->subDays(30)->startOfDay();
                    $endDate = now()->endOfDay();
                    break;
                case 'custom':
                    $startDate = $request->get('date_from') ? Carbon::parse($request->get('date_from'))->startOfDay() : now()->subDays(30)->startOfDay();
                    $endDate = $request->get('date_to') ? Carbon::parse($request->get('date_to'))->endOfDay() : now()->endOfDay();
                    break;
            }

            // Get deposits data
            $deposits = DB::table('student_deposits')
                ->join('students', 'student_deposits.student_id', '=', 'students.student_id')
                ->whereBetween('student_deposits.TransactionDate', [$startDate, $endDate])
                ->whereNull('student_deposits.deleted_at')
                ->select(
                    'student_deposits.DepositID',
                    'student_deposits.student_id',
                    'student_deposits.Amount',
                    'student_deposits.TransactionType',
                    'student_deposits.TransactionDate',
                    'student_deposits.ReferenceNumber',
                    'student_deposits.BalanceBefore',
                    'student_deposits.BalanceAfter',
                    'student_deposits.Notes',
                    DB::raw('CONCAT(students.first_name, " ", students.last_name) as StudentName')
                )
                ->orderBy('student_deposits.TransactionDate', 'desc')
                ->paginate(15);

            // Calculate totals
            $totals = DB::table('student_deposits')
                ->whereBetween('TransactionDate', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->select(
                    DB::raw('COUNT(*) as total_transactions'),
                    DB::raw('SUM(CASE WHEN TransactionType = "DEPOSIT" THEN Amount ELSE 0 END) as total_deposits'),
                    DB::raw('SUM(CASE WHEN TransactionType = "PURCHASE" THEN Amount ELSE 0 END) as total_purchases'),
                    DB::raw('SUM(CASE WHEN TransactionType = "DEPOSIT" THEN Amount WHEN TransactionType = "PURCHASE" THEN -Amount ELSE 0 END) as current_balance')
                )
                ->first();

            // Get student balances
            $studentBalances = DB::table('student_deposits')
                ->whereNull('deleted_at')
                ->select(
                    'student_id',
                    DB::raw('SUM(CASE WHEN TransactionType = "DEPOSIT" THEN Amount ELSE -Amount END) as current_balance')
                )
                ->groupBy('student_id')
                ->orderByDesc('current_balance')
                ->limit(10)
                ->get();

            // Prepare chart data for deposits/withdrawals over time
            $chartData = DB::table('student_deposits')
                ->whereBetween('TransactionDate', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->select(
                    DB::raw('DATE(TransactionDate) as date'),
                    DB::raw('SUM(CASE WHEN TransactionType = "DEPOSIT" THEN Amount ELSE 0 END) as deposits'),
                    DB::raw('SUM(CASE WHEN TransactionType = "PURCHASE" THEN Amount ELSE 0 END) as withdrawals')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $chartDataFormatted = [
                'labels' => $chartData->pluck('date')->map(function($date) {
                    return Carbon::parse($date)->format('M d');
                }),
                'deposits' => $chartData->pluck('deposits'),
                'withdrawals' => $chartData->pluck('withdrawals')
            ];

            // Get top students with their transaction details
            $topStudents = DB::table('student_deposits')
                ->join('students', 'student_deposits.student_id', '=', 'students.student_id')
                ->whereNull('student_deposits.deleted_at')
                ->select(
                    'student_deposits.student_id',
                    DB::raw('CONCAT(students.first_name, " ", students.last_name) as StudentName'),
                    DB::raw('SUM(CASE WHEN TransactionType = "DEPOSIT" THEN Amount ELSE 0 END) as total_deposits'),
                    DB::raw('SUM(CASE WHEN TransactionType = "PURCHASE" THEN Amount ELSE 0 END) as total_withdrawals'),
                    DB::raw('SUM(CASE WHEN TransactionType = "DEPOSIT" THEN Amount ELSE -Amount END) as current_balance')
                )
                ->groupBy('student_deposits.student_id', 'students.first_name', 'students.last_name')
                ->orderByDesc('current_balance')
                ->limit(10)
                ->get();

            return view('pos.reports.deposits', compact(
                'deposits',
                'totals',
                'studentBalances',
                'chartDataFormatted',
                'dateRange',
                'startDate',
                'endDate',
                'topStudents'
            ));

        } catch (\Exception $e) {
            Log::error('Error generating deposits report: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate deposits report. Please try again.');
        }
    }

    public function setStudentLimit(Request $request, $studentId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid limit value',
                    'errors' => $validator->errors()
                ], 422);
            }

            $student = Student::findOrFail($studentId);
            $limit = -abs($request->input('limit')); // Convert to negative value
            
            // Update or insert the limit in student_deposits
            $existingLimit = DB::table('student_deposits')
                ->where('student_id', $studentId)
                ->first();

            if ($existingLimit) {
                DB::table('student_deposits')
                    ->where('student_id', $studentId)
                    ->update(['limit' => $limit]);
            } else {
                DB::table('student_deposits')
                    ->insert([
                        'student_id' => $studentId,
                        'limit' => $limit
                    ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Student limit updated successfully',
                'limit' => abs($limit) // Return positive value for display
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('Student not found: ' . $studentId);
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Student limit update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating student limit: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStudentLimit($studentId)
    {
        try {
            $student = Student::findOrFail($studentId);
            
            // Get limit from student_deposits table
            $limit = DB::table('student_deposits')
                ->where('student_id', $studentId)
                ->value('limit') ?? 0;
            
            return response()->json([
                'success' => true,
                'limit' => abs($limit) // Return positive value for display
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving student limit'
            ], 500);
        }
    }
} 