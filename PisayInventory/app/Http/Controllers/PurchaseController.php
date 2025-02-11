<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    public function index()
    {
        try {
            $purchases = PurchaseOrder::with(['supplier', 'items.item'])
                ->where('IsDeleted', false)
                ->orderBy('OrderDate', 'desc')
                ->get();

            return view('purchases.index', compact('purchases'));
        } catch (\Exception $e) {
            Log::error('Error loading purchases: ' . $e->getMessage());
            return back()->with('error', 'Error loading purchases: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $suppliers = Supplier::where('IsDeleted', false)->get();
            $items = Item::where('IsDeleted', false)->get();

            return view('purchases.create', compact('suppliers', 'items'));
        } catch (\Exception $e) {
            Log::error('Error loading create purchase form: ' . $e->getMessage());
            return back()->with('error', 'Error loading form: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
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

            // Create PO
            $po = PurchaseOrder::create([
                'PONumber' => 'PO-' . date('YmdHis'),
                'SupplierID' => $request->SupplierID,
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
            DB::beginTransaction();

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            $purchaseOrder = PurchaseOrder::findOrFail($id);

            if ($purchaseOrder->Status !== 'Pending') {
                return back()->with('error', 'Only pending purchase orders can be deleted.');
            }

            // Soft delete the purchase order
            $purchaseOrder->update([
                'IsDeleted' => true,
                'DeletedByID' => $currentEmployee->EmployeeID,
                'DateDeleted' => now()
            ]);

            // Soft delete all related items
            PurchaseOrderItem::where('PurchaseOrderID', $id)
                ->update([
                    'IsDeleted' => true,
                    'DeletedByID' => $currentEmployee->EmployeeID,
                    'DateDeleted' => now()
                ]);

            DB::commit();
            return redirect()->route('purchases.index')
                ->with('success', 'Purchase order deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting purchase order: ' . $e->getMessage());
            return back()->with('error', 'Error deleting purchase order: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            DB::beginTransaction();

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            $purchaseOrder = PurchaseOrder::findOrFail($id);

            // Restore the purchase order
            $purchaseOrder->update([
                'IsDeleted' => false,
                'RestoredById' => $currentEmployee->EmployeeID,
                'DateRestored' => now()
            ]);

            // Restore all related items
            PurchaseOrderItem::where('PurchaseOrderID', $id)
                ->update([
                    'IsDeleted' => false,
                    'RestoredById' => $currentEmployee->EmployeeID,
                    'DateRestored' => now()
                ]);

            DB::commit();
            return redirect()->route('purchases.index')
                ->with('success', 'Purchase order restored successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restoring purchase order: ' . $e->getMessage());
            return back()->with('error', 'Error restoring purchase order: ' . $e->getMessage());
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
