<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Item;
use App\Models\UnitOfMeasure;
use App\Models\Classification;
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
            'item.classification', 
            'unit_of_measure', 
            'created_by_user'
        ]);

        if (!$showDeleted) {
            $query->where('IsDeleted', 0);
        }

        $purchases = $query->orderBy('DateCreated', 'desc')
            ->paginate(10);

        // Get dropdown data
        $items = Item::where('IsDeleted', 0)->orderBy('ItemName')->get();
        $units = UnitOfMeasure::active()->orderBy('UnitName')->get();
        $classifications = Classification::safeGetClassifications();

        return view('purchases.index', compact('purchases', 'items', 'units', 'classifications'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ItemId' => 'required|exists:items,ItemId',
            'UnitOfMeasureId' => 'nullable|exists:unitofmeasure,UnitOfMeasureId',
            'ClassificationId' => 'nullable|exists:classification,ClassificationId',
            'Quantity' => 'required|integer|min:1',
            'StocksAdded' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Create purchase record
            $purchase = new Purchase();
            $purchase->fill($validated);
            $purchase->DateCreated = now();
            $purchase->CreatedById = Auth::id();
            $purchase->IsDeleted = 0;
            $purchase->save();

            // Update item stocks
            $item = Item::findOrFail($validated['ItemId']);
            $item->StocksAvailable += $validated['StocksAdded'];
            $item->save();

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase record created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase creation failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Failed to create purchase record: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);

        $validated = $request->validate([
            'ItemId' => 'required|exists:items,ItemId',
            'UnitOfMeasureId' => 'nullable|exists:unitofmeasure,UnitOfMeasureId',
            'ClassificationId' => 'nullable|exists:classification,ClassificationId',
            'Quantity' => 'required|integer|min:1',
            'StocksAdded' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Calculate stock difference
            $stockDifference = $validated['StocksAdded'] - $purchase->StocksAdded;

            // Update purchase record
            $purchase->fill($validated);
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
