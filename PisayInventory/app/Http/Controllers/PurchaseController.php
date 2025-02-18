<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Employee;
use App\Models\Purchase;
use App\Models\RolePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    private function getUserPermissions()
    {
        $userRole = auth()->user()->role;
        return RolePolicy::whereHas('role', function($query) use ($userRole) {
            $query->where('RoleName', $userRole);
        })->where('Module', 'Purchasing Management')->first();
    }

    public function index()
    {
        try {
            $userPermissions = $this->getUserPermissions();
            
            // Check if user has View permission
            if (!$userPermissions || !$userPermissions->CanView) {
                return redirect()->back()->with('error', 'You do not have permission to view purchases.');
            }

            // Active purchase orders (Received status)
            $purchases = PurchaseOrder::with([
                'supplier', 
                'items.item',
                'createdBy', 
                'modifiedBy'
            ])
            ->where('IsDeleted', false)
            ->where('Status', 'Received')
            ->orderBy('DateCreated', 'desc')
            ->get();

            // Pending purchase orders
            $pendingPurchases = PurchaseOrder::with([
                'supplier', 
                'items.item',
                'createdBy', 
                'modifiedBy'
            ])
            ->where('IsDeleted', false)
            ->where('Status', 'Pending')
            ->orderBy('DateCreated', 'desc')
            ->get();

            // Deleted purchase orders
            $deletedPurchases = PurchaseOrder::with([
                'supplier', 
                'items.item',
                'createdBy', 
                'modifiedBy', 
                'deletedBy'
            ])
            ->where('IsDeleted', true)
            ->orderBy('DateDeleted', 'desc')
            ->get();

            $suppliers = Supplier::where('IsDeleted', false)->get();
            $items = Item::with('supplier')
                ->where('IsDeleted', false)
                ->get();

            return view('purchases.index', [
                'purchases' => $purchases,
                'pendingPurchases' => $pendingPurchases,
                'deletedPurchases' => $deletedPurchases,
                'suppliers' => $suppliers,
                'items' => $items,
                'userPermissions' => $userPermissions
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading purchase orders: ' . $e->getMessage());
            return back()->with('error', 'Error loading purchase orders: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $suppliers = Supplier::where('IsDeleted', false)->get();
            $items = Item::with('supplier')
                ->where('IsDeleted', false)
                ->get();

            return view('purchases.create', compact('suppliers', 'items'));
        } catch (\Exception $e) {
            Log::error('Error loading create purchase form: ' . $e->getMessage());
            return back()->with('error', 'Error loading form: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $userPermissions = $this->getUserPermissions();
            
            // Check if user has Add permission
            if (!$userPermissions || !$userPermissions->CanAdd) {
                return redirect()->back()->with('error', 'You do not have permission to add purchases.');
            }

            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.ItemId' => 'required|exists:items,ItemId',
                'items.*.SupplierID' => 'required|exists:suppliers,SupplierID',
                'items.*.Quantity' => 'required|integer|min:1',
                'items.*.UnitPrice' => 'required|numeric|min:0'
            ]);

            DB::beginTransaction();

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            // Get the supplier ID from the first item (or you could group by supplier)
            $supplierID = $request->items[0]['SupplierID'];

            // Create PO
            $po = PurchaseOrder::create([
                'PONumber' => 'PO-' . date('YmdHis'),
                'SupplierID' => $supplierID, // Use the supplier ID from the first item
                'OrderDate' => now(),
                'Status' => 'Pending',
                'TotalAmount' => 0,
                'DateCreated' => now(),
                'CreatedByID' => $currentEmployee->EmployeeID,
                'IsDeleted' => false
            ]);

            $totalAmount = 0;

            // Create PO Items
            foreach ($request->items as $item) {
                $totalPrice = $item['Quantity'] * $item['UnitPrice'];
                $totalAmount += $totalPrice;

                PurchaseOrderItem::create([
                    'PurchaseOrderID' => $po->PurchaseOrderID,
                    'ItemId' => $item['ItemId'],
                    'Quantity' => $item['Quantity'],
                    'UnitPrice' => $item['UnitPrice'],
                    'TotalPrice' => $totalPrice,
                    'DateCreated' => now(),
                    'CreatedByID' => $currentEmployee->EmployeeID,
                    'IsDeleted' => false
                ]);
            }

            // Update PO total
            $po->update([
                'TotalAmount' => $totalAmount,
                'ModifiedByID' => $currentEmployee->EmployeeID,
                'DateModified' => now()
            ]);

            DB::commit();
            return redirect()->route('purchases.index')
                ->with('success', 'Purchase order created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating purchase order: ' . $e->getMessage());
            return back()->with('error', 'Error creating purchase order: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $purchase = PurchaseOrder::with(['supplier', 'items.item', 'createdBy', 'modifiedBy'])
                ->findOrFail($id);

            return view('purchases.show', compact('purchase'));
        } catch (\Exception $e) {
            Log::error('Error showing purchase order: ' . $e->getMessage());
            return back()->with('error', 'Error showing purchase order: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $userPermissions = $this->getUserPermissions();
            
            // Check if user has Delete permission
            if (!$userPermissions || !$userPermissions->CanDelete) {
                return redirect()->back()->with('error', 'You do not have permission to delete purchases.');
            }

            DB::beginTransaction();

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            $purchaseOrder = PurchaseOrder::findOrFail($id);
            
            // Soft delete the purchase order
            $purchaseOrder->update([
                'IsDeleted' => true,
                'DeletedByID' => $currentEmployee->EmployeeID,
                'DateDeleted' => now()
            ]);

            // Also soft delete related purchase order items
            PurchaseOrderItem::where('PurchaseOrderID', $id)
                ->update([
                    'IsDeleted' => true,
                    'DeletedByID' => $currentEmployee->EmployeeID,
                    'DateDeleted' => now()
                ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Purchase order deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting purchase order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $userPermissions = $this->getUserPermissions();
            
            // Check if user has Edit permission for restore
            if (!$userPermissions || !$userPermissions->CanEdit) {
                return redirect()->back()->with('error', 'You do not have permission to restore purchases.');
            }

            Log::info('Starting restore process for purchase order: ' . $id);
            DB::beginTransaction();

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            $purchaseOrder = PurchaseOrder::findOrFail($id);

            // Restore the purchase order
            $purchaseOrder->update([
                'IsDeleted' => false,
                'RestoredById' => $currentEmployee->EmployeeID,
                'DateRestored' => now(),
                'DeletedByID' => null,
                'DateDeleted' => null
            ]);

            // Restore all related items
            PurchaseOrderItem::where('PurchaseOrderID', $id)
                ->update([
                    'IsDeleted' => false,
                    'RestoredById' => $currentEmployee->EmployeeID,
                    'DateRestored' => now(),
                    'DeletedByID' => null,
                    'DateDeleted' => null
                ]);

            DB::commit();
            Log::info('Purchase order restored successfully');
            return response()->json(['success' => true, 'message' => 'Purchase order restored successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restoring purchase order: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Error restoring purchase order: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        try {
            $purchase = PurchaseOrder::with(['supplier', 'items.item'])
                ->where('IsDeleted', false)
                ->findOrFail($id);

            if ($purchase->Status !== 'Pending') {
                return back()->with('error', 'Only pending purchase orders can be edited.');
            }

            $suppliers = Supplier::where('IsDeleted', false)->get();
            $items = Item::where('IsDeleted', false)->get();

            return view('purchases.edit', compact('purchase', 'suppliers', 'items'));
        } catch (\Exception $e) {
            Log::error('Error loading edit purchase form: ' . $e->getMessage());
            return back()->with('error', 'Error loading form: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $userPermissions = $this->getUserPermissions();
            
            // Check if user has Edit permission
            if (!$userPermissions || !$userPermissions->CanEdit) {
                return redirect()->back()->with('error', 'You do not have permission to edit purchases.');
            }

            $request->validate([
                'SupplierID' => 'required|exists:suppliers,SupplierID',
                'items' => 'required|array|min:1',
                'items.*.ItemId' => 'required|exists:items,ItemId',
                'items.*.Quantity' => 'required|integer|min:1',
                'items.*.UnitPrice' => 'required|numeric|min:0'
            ]);

            DB::beginTransaction();

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            $purchaseOrder = PurchaseOrder::findOrFail($id);

            if ($purchaseOrder->Status !== 'Pending') {
                return back()->with('error', 'Only pending purchase orders can be edited.');
            }

            // Update PO
            $purchaseOrder->update([
                'SupplierID' => $request->SupplierID,
                'ModifiedByID' => $currentEmployee->EmployeeID,
                'DateModified' => now()
            ]);

            // Delete existing items
            PurchaseOrderItem::where('PurchaseOrderID', $id)->delete();

            $totalAmount = 0;

            // Create new PO Items
            foreach ($request->items as $item) {
                $totalPrice = $item['Quantity'] * $item['UnitPrice'];
                $totalAmount += $totalPrice;

                PurchaseOrderItem::create([
                    'PurchaseOrderID' => $purchaseOrder->PurchaseOrderID,
                    'ItemId' => $item['ItemId'],
                    'Quantity' => $item['Quantity'],
                    'UnitPrice' => $item['UnitPrice'],
                    'TotalPrice' => $totalPrice,
                    'DateCreated' => now(),
                    'CreatedByID' => $currentEmployee->EmployeeID,
                    'IsDeleted' => false
                ]);
            }

            // Update PO total
            $purchaseOrder->update([
                'TotalAmount' => $totalAmount,
                'ModifiedByID' => $currentEmployee->EmployeeID,
                'DateModified' => now()
            ]);

            DB::commit();
            return redirect()->route('purchases.show', $id)
                ->with('success', 'Purchase order updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating purchase order: ' . $e->getMessage());
            return back()->with('error', 'Error updating purchase order: ' . $e->getMessage())
                ->withInput();
        }
    }
}
