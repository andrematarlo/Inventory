<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $showDeleted = $request->input('show_deleted', false);

        $query = Purchase::with([
            'item',
            'supplier',
            'created_by',
            'modified_by',
            'deleted_by'
        ]);

        if (!$showDeleted) {
            $query->active();
        }

        $purchases = $query->orderBy('DateCreated', 'desc')
            ->paginate(10);

        // Get dropdown data
        $items = Item::active()->orderBy('ItemName')->get();
        $suppliers = Supplier::active()->orderBy('CompanyName')->get();

        return view('purchases.index', compact('purchases', 'items', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ItemId' => 'required|exists:items,ItemId',
            'SupplierId' => 'required|exists:suppliers,SupplierID',
            'Quantity' => 'required|integer|min:1',
            'UnitPrice' => 'required|numeric|min:0',
            'PurchaseOrderNumber' => 'nullable|string|max:255',
            'PurchaseDate' => 'required|date',
            'DeliveryDate' => 'nullable|date|after_or_equal:PurchaseDate',
            'Status' => 'nullable|string|max:255',
            'Notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $purchase = new Purchase();
            $purchase->fill($validated);
            $purchase->TotalAmount = $validated['Quantity'] * $validated['UnitPrice'];
            $purchase->CreatedById = Auth::id();
            $purchase->DateCreated = now();
            $purchase->save();

            // Update item stock
            $item = Item::findOrFail($validated['ItemId']);
            $item->StocksAvailable += $validated['Quantity'];
            $item->save();

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Purchase created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase creation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to create purchase: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);

        $validated = $request->validate([
            'ItemId' => 'required|exists:items,ItemId',
            'SupplierId' => 'required|exists:suppliers,SupplierID',
            'Quantity' => 'required|integer|min:1',
            'UnitPrice' => 'required|numeric|min:0',
            'PurchaseOrderNumber' => 'nullable|string|max:255',
            'PurchaseDate' => 'required|date',
            'DeliveryDate' => 'nullable|date|after_or_equal:PurchaseDate',
            'Status' => 'nullable|string|max:255',
            'Notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Calculate stock difference
            $stockDifference = $validated['Quantity'] - $purchase->Quantity;

            // Update purchase record
            $purchase->fill($validated);
            $purchase->TotalAmount = $validated['Quantity'] * $validated['UnitPrice'];
            $purchase->DateModified = now();
            $purchase->ModifiedById = Auth::id();
            $purchase->save();

            // Update item stocks
            $item = Item::findOrFail($validated['ItemId']);
            $item->StocksAvailable += $stockDifference;
            $item->save();

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase record updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase update failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Failed to update purchase record: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $purchase = Purchase::findOrFail($id);

        try {
            DB::beginTransaction();

            // Soft delete the purchase
            $purchase->softDelete();

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase record deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase deletion failed: ' . $e->getMessage());

            return back()
                ->with('error', 'Failed to delete purchase record: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        $purchase = Purchase::withTrashed()->findOrFail($id);

        try {
            DB::beginTransaction();

            // Restore the purchase
            $purchase->restore();

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase record restored successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase restoration failed: ' . $e->getMessage());

            return back()
                ->with('error', 'Failed to restore purchase record: ' . $e->getMessage());
        }
    }
}
