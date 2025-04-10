<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\PurchaseItem;
use App\Models\RolePolicy;
use App\Enums\PurchaseStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Employee;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getUserPermissions($module = null)
    {
        return parent::getUserPermissions('Purchasing Management');
    }

    public function index()
    {
        try {
            $userPermissions = $this->getUserPermissions();

            $purchases = Purchase::with(['supplier', 'items.item', 'createdBy', 'modifiedBy'])
                ->where('IsDeleted', false)
                ->when(request('search'), function($query, $search) {
                    $query->where('PONumber', 'like', "%{$search}%")
                          ->orWhereHas('supplier', function($q) use ($search) {
                              $q->where('CompanyName', 'like', "%{$search}%");
                          });
                })
                ->paginate(request('per_page', 10));

            $pendingPurchases = Purchase::with(['supplier', 'items.item', 'createdBy', 'modifiedBy'])
                ->where('IsDeleted', false)
                ->where('Status', PurchaseStatus::PENDING->value)
                ->get();

            $deletedPurchases = Purchase::with(['supplier', 'items.item', 'createdBy', 'modifiedBy', 'deletedBy'])
                ->where('IsDeleted', true)
                ->get();

            // Load items with their active suppliers
            $items = Item::with(['suppliers' => function($query) {
                $query->select('suppliers.SupplierID', 'suppliers.CompanyName')
                      ->where('items_suppliers.IsDeleted', false);
            }])->where('IsDeleted', false)->get();

            return view('purchases.index', compact(
                'purchases',
                'pendingPurchases',
                'deletedPurchases',
                'items',
                'userPermissions'
            ));

        } catch (\Exception $e) {
            \Log::error('Error loading purchase orders:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error loading purchase orders: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $purchase = Purchase::with([
                'supplier',
                'items.item',
                'createdBy',
                'modifiedBy',
                'deletedBy',
                'restoredBy'
            ])->findOrFail($id);

            $userPermissions = $this->getUserPermissions();

            return view('purchases.show', compact('purchase', 'userPermissions'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading purchase order: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            // Get the authenticated user's ID and find the employee
            $userAccountId = Auth::id();
            \Log::info('Auth ID:', ['user_account_id' => $userAccountId]); // Debug log
            
            $employee = Employee::where('UserAccountID', $userAccountId)->first();
            \Log::info('Employee found:', ['employee' => $employee]); // Debug log
            
            if (!$employee) {
                throw new \Exception('Employee not found for UserAccountID: ' . $userAccountId);
            }
            
            $purchase = Purchase::findOrFail($id);
            $purchase->softDelete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Purchase order deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting purchase order:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            DB::beginTransaction();
            
            // Get the authenticated user's ID and find the employee
            $userAccountId = Auth::id();
            \Log::info('Auth ID for restore:', ['user_account_id' => $userAccountId]);
            
            $employee = Employee::where('UserAccountID', $userAccountId)->first();
            \Log::info('Employee found for restore:', ['employee' => $employee]);
            
            if (!$employee) {
                throw new \Exception('Employee not found for UserAccountID: ' . $userAccountId);
            }
            
            $purchase = Purchase::findOrFail($id);
            $purchase->restore();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order restored successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error restoring purchase order:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error restoring purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Get the employee record for the authenticated user
            $employee = Employee::where('UserAccountID', Auth::id())->first();
            
            if (!$employee) {
                throw new \Exception('Employee record not found for the authenticated user.');
            }

            // Calculate initial total amount
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['Quantity'] * $item['UnitPrice'];
            }

            // Create the purchase order
            $purchase = new Purchase();
            $purchase->PONumber = $request->PONumber ?? 'PO-' . date('YmdHis');
            $purchase->OrderDate = now();
            $purchase->Status = PurchaseStatus::PENDING;
            $purchase->CreatedByID = $employee->EmployeeID;
            $purchase->DateCreated = now();
            $purchase->SupplierID = $request->items[0]['SupplierID'];
            $purchase->TotalAmount = $totalAmount;
            $purchase->save();

            // Add purchase items
            foreach ($request->items as $item) {
                $itemTotal = $item['Quantity'] * $item['UnitPrice'];
                $purchaseItem = new PurchaseItem([
                    'PurchaseOrderID' => $purchase->PurchaseOrderID,
                    'ItemId' => $item['ItemId'],
                    'Quantity' => $item['Quantity'],
                    'UnitPrice' => $item['UnitPrice'],
                    'TotalPrice' => $itemTotal,
                    'DateCreated' => now(),
                    'CreatedByID' => $employee->EmployeeID
                ]);
                $purchaseItem->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order created successfully',
                'redirect' => route('purchases.show', $purchase->PurchaseOrderID)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating purchase order:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        try {
            // Get user permissions
            $userPermissions = $this->getUserPermissions();

            // Load items with their active suppliers
            $items = Item::with(['suppliers' => function($query) {
                $query->where('items_suppliers.IsDeleted', false)
                      ->select('suppliers.SupplierID', 'suppliers.CompanyName');
            }])->where('IsDeleted', false)
              ->get()
              ->map(function($item) {
                  // Format suppliers data for the view
                  $item->formatted_suppliers = $item->suppliers->map(function($supplier) {
                      return [
                          'SupplierID' => $supplier->SupplierID,
                          'CompanyName' => $supplier->CompanyName
                      ];
                  });
                  return $item;
              });

            // Load all active suppliers for the initial dropdown
            $suppliers = Supplier::where('IsDeleted', false)
                               ->select('SupplierID', 'CompanyName')
                               ->get();

            return view('purchases.create', compact('items', 'suppliers', 'userPermissions'));

        } catch (\Exception $e) {
            \Log::error('Error loading create purchase form:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error loading create purchase form: ' . $e->getMessage());
        }
    }

    // Add other controller methods as needed...
}
