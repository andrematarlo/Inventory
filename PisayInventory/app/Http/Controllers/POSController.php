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
    public function index()
    {
        $orders = POSOrder::with(['student', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

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
                        
        $categories = DB::table('classification')
                      ->whereNull('deleted_at')
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
                Log::debug('Parsed cart items from JSON string', ['cartItems' => $cartItems]);
            }
            
            // Debug request data
            Log::debug('Order request data:', [
                'student_id' => $request->student_id,
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_type,
                'items' => $cartItems
            ]);
            
            // Generate order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(
                POSOrder::whereDate('created_at', today())->count() + 1, 
                4, 
                '0', 
                STR_PAD_LEFT
            );
            
            Log::debug('Generated order number: ' . $orderNumber);
            
            // Debug table structure
            Log::debug('POS Orders table structure:', [
                'columns' => DB::getSchemaBuilder()->getColumnListing('pos_orders')
            ]);
            
            // Create the order
            $order = POSOrder::create([
                'student_id' => $request->student_id,
                'TotalAmount' => $request->total_amount,
                'PaymentMethod' => $request->payment_type,
                'Status' => 'pending',
                'OrderNumber' => $orderNumber
            ]);
            
            Log::debug('Order created:', ['order_id' => $order->OrderID, 'order_data' => $order->toArray()]);
            
            // Add order items
            foreach ($cartItems as $item) {
                // Check if it's a custom item or menu item
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
                        
                        Log::debug('Order item created:', ['order_item' => $orderItem->toArray()]);
                    } else {
                        // If no ID but has name and price, treat as custom item
                        Log::debug('Processing item without ID as custom:', ['item' => $item]);
                        
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
                    }
                }
            }
            
            DB::commit();
            Log::debug('Order transaction committed successfully');
            
            $responseData = [
                'success' => true,
                'message' => 'Order created successfully',
                'order' => $order,
                'alert' => [
                    'type' => 'success',
                    'title' => 'Success!',
                    'text' => 'Order #' . $orderNumber . ' created successfully'
                ]
            ];
            
            return response()->json($responseData);
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating order: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Get database structure for debugging
            $tableStructure = [];
            try {
                $tableStructure['pos_orders'] = DB::getSchemaBuilder()->getColumnListing('pos_orders');
                $tableStructure['pos_order_items'] = DB::getSchemaBuilder()->getColumnListing('pos_order_items');
                $tableStructure['menu_items'] = DB::getSchemaBuilder()->getColumnListing('menu_items');
            } catch (\Exception $ex) {
                $tableStructure['error'] = $ex->getMessage();
            }
            
            $responseData = [
                'success' => false,
                'message' => 'Error creating order: ' . $e->getMessage(),
                'details' => $e->getTraceAsString(),
                'debug' => [
                    'request' => $request->all(),
                    'table_structure' => $tableStructure
                ],
                'alert' => [
                    'type' => 'error',
                    'title' => 'Order Failed',
                    'text' => 'Error: ' . $e->getMessage(),
                    'footer' => 'Please check the console for more details'
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
            ->sum('Amount');

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
                
                $balance = CashDeposit::where('student_id', $order->student_id)
                    ->whereNull('deleted_at')
                    ->sum('Amount');
                
                if ($balance < $order->TotalAmount) {
                    return redirect()->back()->with('error', 'Insufficient deposit balance');
                }
                
                // Create new deposit record with negative amount
                CashDeposit::create([
                    'student_id' => $order->student_id,
                    'Amount' => -$order->TotalAmount,
                    'ReferenceType' => 'order_payment',
                    'ReferenceID' => $order->OrderID,
                    'Status' => 'completed',
                    'ProcessedBy' => Auth::id() ?? 1,
                    'ProcessedAt' => now()
                ]);
            }
            
            // Create payment transaction record
            DB::table('payment_transactions')->insert([
                'OrderId' => $order->OrderID,
                'Amount' => $order->TotalAmount,
                'PaymentType' => $validated['payment_type'],
                'Status' => 'completed',
                'ReferenceNumber' => 'PAY-' . $order->OrderNumber,
                'CreatedById' => Auth::id() ?? 1,
                'DateCreated' => now(),
                'ModifiedById' => Auth::id() ?? 1,
                'DateModified' => now()
            ]);
            
            // Update order status
            DB::table('pos_orders')
                ->where('OrderID', $orderId)
                ->update([
                    'Status' => 'completed',
                    'updated_at' => now()
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
            'category_id' => 'required|exists:classification,ClassificationID',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            
            // Create menu item
            $menuItemId = DB::table('menu_items')->insertGetId([
                'ItemName' => $request->item_name,
                'Price' => $request->price,
                'ClassificationID' => $request->category_id,
                'Description' => $request->description,
                'IsAvailable' => true,
                'IsDeleted' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            DB::commit();
            
            $successMessage = "Menu item '{$request->item_name}' added successfully!";
            
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
                // Only increment stock for regular inventory items
                DB::table('menu_items')
                    ->where('ItemID', $item->ItemID)
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
            $order = DB::table('pos_orders')
                ->leftJoin('students', 'pos_orders.student_id', '=', 'students.student_id')
                ->where('pos_orders.OrderID', $orderId)
                ->whereNull('pos_orders.deleted_at')
                ->select('pos_orders.*', 'students.first_name as FirstName', 'students.last_name as LastName', 'students.student_id as StudentNumber')
                ->first();
                
            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }
            
            // Get order items
            $items = DB::table('pos_order_items')
                ->leftJoin('menu_items', 'pos_order_items.ItemID', '=', 'menu_items.ItemID')
                ->where('pos_order_items.OrderID', $orderId)
                ->select(
                    'pos_order_items.*',
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