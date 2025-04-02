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
                'customer_name' => $request->customer_name,
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
                        
                        // Update stock in menu_items table
                        $menuItem->StocksAvailable -= $item['quantity'];
                        $menuItem->save();
                        
                        Log::debug('Stock updated:', [
                            'item_id' => $menuItem->MenuItemID,
                            'item_name' => $menuItem->ItemName,
                            'previous_stock' => $menuItem->StocksAvailable + $item['quantity'],
                            'quantity_ordered' => $item['quantity'],
                            'new_stock' => $menuItem->StocksAvailable
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
                    'text' => "Order #{$orderNumber} has been created successfully.",
                    'footer' => 'Stock levels have been updated automatically.'
                ],
                'updated_stocks' => collect($cartItems)->map(function($item) {
                    if (!empty($item['id']) && empty($item['isCustom'])) {
                        $menuItem = MenuItem::find($item['id']);
                        return [
                            'item_name' => $menuItem->ItemName,
                            'new_stock' => $menuItem->StocksAvailable
                        ];
                    }
                })->filter()
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
            ->sum(DB::raw('CASE WHEN TransactionType = "DEPOSIT" THEN Amount ELSE -Amount END'));

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
                ->sum(DB::raw('CASE WHEN TransactionType = "DEPOSIT" THEN Amount WHEN TransactionType = "WITHDRAWAL" THEN -Amount ELSE 0 END'));
                
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
                    ->sum(DB::raw('CASE WHEN TransactionType = "DEPOSIT" THEN Amount ELSE -Amount END'));

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
                    'TransactionType' => 'WITHDRAWAL',
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
            $menuItems = MenuItem::with(['classification', 'unitOfMeasure'])
                ->orderBy('ItemName')
                ->get();

            // Get all classifications for the filter dropdown
            $categories = Classification::orderBy('ClassificationName')->get();

            return view('pos.menu-items.index', compact('menuItems', 'categories'));

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
                'ClassificationId' => 'required|exists:classification,ClassificationId',
                'Description' => 'nullable|string',
                'StocksAvailable' => 'required|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            $menuItem->ClassificationId = $request->ClassificationId;
            $menuItem->Description = $request->Description;
            $menuItem->StocksAvailable = $request->StocksAvailable;
            $menuItem->IsAvailable = true;
            $menuItem->IsDeleted = false;
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('menu-items', 'public');
                $menuItem->image_path = $imagePath;
            }

            $menuItem->save();

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
            // Log the incoming request data
            Log::info('Update menu item request:', [
                'id' => $id,
                'request_data' => $request->all()
            ]);

            $validator = Validator::make($request->all(), [
                'ItemName' => 'required|string|max:255',
                'Price' => 'required|numeric|min:0',
                'ClassificationID' => 'required|exists:classification,ClassificationId',
                'Description' => 'nullable|string',
                'StocksAvailable' => 'required|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
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

            // Log the current state
            Log::info('Current menu item state:', [
                'id' => $id,
                'current_data' => $menuItem->toArray()
            ]);
                
            $menuItem->ItemName = $request->ItemName;
            $menuItem->Price = $request->Price;
            $menuItem->ClassificationID = $request->ClassificationID;
            $menuItem->Description = $request->Description;
            $menuItem->StocksAvailable = $request->StocksAvailable;
            
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

            DB::commit();
            
            // Log the final state
            Log::info('Menu item updated successfully:', [
                'id' => $id,
                'updated_data' => $menuItem->toArray()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Menu item updated successfully',
                'data' => $menuItem
            ]);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Menu item not found:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update menu item:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
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
                'customer_name' => 'required|string|max:255',
                'status' => 'required|in:pending,paid,preparing,ready,completed,cancelled'
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
            
            $order->customer_name = $request->customer_name;
            $order->Status = $request->status;
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
            Log::info('Checking balance for student ID: ' . $studentId);

            // Get the latest balance for the student
            $latestDeposit = DB::table('student_deposits')
                ->where('student_id', $studentId)
                ->whereNull('deleted_at')
                ->orderBy('TransactionDate', 'desc')
                ->first();

            Log::info('Latest deposit record:', ['deposit' => $latestDeposit]);

            if (!$latestDeposit) {
                Log::warning('No deposit records found for student ID: ' . $studentId);
                return response()->json([
                    'success' => false,
                    'message' => 'No deposit records found for this student.'
                ]);
            }

            // Get student information
            $student = DB::table('students')
                ->where('student_id', $studentId)
                ->first();

            Log::info('Student record:', ['student' => $student]);

            if (!$student) {
                Log::warning('Student not found with ID: ' . $studentId);
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found.'
                ]);
            }

            $response = [
                'success' => true,
                'student' => [
                    'id' => $student->student_id,
                    'name' => $student->first_name . ' ' . $student->last_name
                ],
                'balance' => $latestDeposit->BalanceAfter
            ];

            Log::info('Balance check response:', $response);
            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Error checking student balance: ' . $e->getMessage(), [
                'student_id' => $studentId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while checking the student balance.',
                'debug_message' => $e->getMessage()
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
} 