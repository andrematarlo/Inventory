<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\Classification;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventories = Inventory::with(['item', 'employee'])
            ->where('IsDeleted', 0)
            ->latest('DateCreated')
            ->paginate(10);

        $items = Item::where('IsDeleted', 0)->get();
        
        return view('inventory.index', compact('inventories', 'items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request
            $validated = $request->validate([
                'ItemId' => 'required|exists:items,ItemId',
                'StocksAdded' => 'required|numeric',
                'remarks' => 'nullable|string'
            ]);

            $item = Item::findOrFail($validated['ItemId']);

            // Create inventory record
            $inventory = new Inventory();
            $inventory->ItemId = $validated['ItemId'];
            $inventory->StocksAdded = $validated['StocksAdded'];
            $inventory->StocksAvailable = $item->StocksAvailable + $validated['StocksAdded'];
            $inventory->DateCreated = now();
            $inventory->CreatedById = Auth::id();
            $inventory->IsDeleted = false;
            $inventory->save();

            // Update item stock
            $item->StocksAvailable = $inventory->StocksAvailable;
            $item->ModifiedById = Auth::id();
            $item->DateModified = now();
            $item->save();

            DB::commit();
            return back()->with('success', 'Inventory updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Inventory update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update inventory. Please try again.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $inventory = Inventory::findOrFail($id);
            $oldQuantity = $inventory->Quantity;

            $request->validate([
                'Quantity' => 'required|integer',
                'InventoryDate' => 'required|date'
            ]);

            // Update inventory record
            $inventory->update([
                'Quantity' => $request->Quantity,
                'InventoryDate' => $request->InventoryDate,
                'ModifiedById' => Auth::id(),
                'DateModified' => now()
            ]);

            // Update item stock
            $item = $inventory->item;
            $item->StocksAvailable += ($request->Quantity - $oldQuantity);
            $item->ModifiedById = Auth::id();
            $item->DateModified = now();
            $item->save();

            DB::commit();
            return back()->with('success', 'Inventory updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Inventory update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update inventory')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $inventory = Inventory::findOrFail($id);
            
            // Update item stock before deleting inventory record
            $item = $inventory->item;
            $item->StocksAvailable -= $inventory->Quantity;
            $item->ModifiedById = Auth::id();
            $item->DateModified = now();
            $item->save();

            // Soft delete inventory record
            $inventory->update([
                'IsDeleted' => true,
                'DeletedById' => Auth::id(),
                'DateDeleted' => now()
            ]);

            DB::commit();
            return back()->with('success', 'Inventory record deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Inventory deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete inventory record');
        }
    }
}
