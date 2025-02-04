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
        $inventories = Inventory::with(['item', 'created_by_user'])
            ->active()
            ->latest('DateCreated')
            ->paginate(10);

        // Get all active items
        $items = Item::with(['classification'])
            ->where('IsDeleted', 0)
            ->orderBy('ItemName')
            ->get();
        
        // Debug log to check items
        \Log::info('All items:', $items->map(function($item) {
            return [
                'ItemId' => $item->ItemId,
                'ItemName' => $item->ItemName,
                'ClassificationId' => $item->ClassificationId,
                'IsDeleted' => $item->IsDeleted,
                'StocksAvailable' => $item->StocksAvailable,
                'Classification' => $item->classification ? $item->classification->ClassificationName : 'N/A'
            ];
        })->toArray());

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
                'StocksAdded' => 'required|integer|min:1',
                'type' => 'required|in:in,out'
            ]);

            // Get item with its classification
            $item = Item::findOrFail($validated['ItemId']);
            
            // Debug log
            \Log::info('Before update:', [
                'ItemId' => $validated['ItemId'],
                'Type' => $validated['type'],
                'Quantity' => $validated['StocksAdded'],
                'Current Item Stock' => $item->StocksAvailable
            ]);

            // Get existing inventory record or create new one
            $inventory = Inventory::where('ItemId', $validated['ItemId'])
                ->where('IsDeleted', false)
                ->first();

            // Debug log
            \Log::info('Current Inventory:', [
                'Found' => $inventory ? 'Yes' : 'No',
                'Current Stock' => $inventory ? $inventory->StocksAvailable : 0
            ]);

            if (!$inventory) {
                $inventory = new Inventory();
                $inventory->ItemId = $validated['ItemId'];
                $inventory->ClassificationId = $item->ClassificationId;
                $inventory->DateCreated = Carbon::now()->format('Y-m-d H:i:s');
                $inventory->CreatedById = Auth::user()->UserAccountID;
                $inventory->IsDeleted = false;
                $inventory->StocksAdded = 0;
                $inventory->StockOut = 0;
                $inventory->StocksAvailable = 0;
            }

            if ($validated['type'] === 'in') {
                // Stock In
                $inventory->StocksAdded += $validated['StocksAdded'];
                $inventory->StocksAvailable += $validated['StocksAdded'];
                $item->StocksAvailable += $validated['StocksAdded'];
            } else {
                // Stock Out
                if ($inventory->StocksAvailable < $validated['StocksAdded']) {
                    DB::rollBack();
                    return back()->with('error', 'Not enough stock available for removal.')
                                ->withInput();
                }
                
                // Direct SQL update for inventory table
                DB::table('inventory')
                    ->where('ItemId', $validated['ItemId'])
                    ->where('IsDeleted', false)
                    ->update([
                        'StockOut' => DB::raw('StockOut + ' . $validated['StocksAdded']),
                        'StocksAvailable' => DB::raw('StocksAvailable - ' . $validated['StocksAdded']),
                        'DateModified' => Carbon::now()->format('Y-m-d H:i:s'),
                        'ModifiedById' => Auth::user()->UserAccountID
                    ]);

                // Update item table
                $item->StocksAvailable -= $validated['StocksAdded'];
            }

            // Debug log
            \Log::info('After update:', [
                'New Item Stock' => $item->StocksAvailable,
                'New Inventory Stock' => DB::table('inventory')
                    ->where('ItemId', $validated['ItemId'])
                    ->where('IsDeleted', false)
                    ->value('StocksAvailable')
            ]);

            // Update item
            $item->DateModified = Carbon::now()->format('Y-m-d H:i:s');
            $item->ModifiedById = Auth::user()->UserAccountID;
            $item->save();

            DB::commit();
            return redirect()->route('inventory.index')
                ->with('success', 'Inventory updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Inventory update failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()->with('error', 'Failed to update inventory: ' . $e->getMessage())
                        ->withInput();
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
            
            // Update item stock before soft deleting
            $item = $inventory->item;
            $item->StocksAvailable -= $inventory->StocksAdded;
            $item->ModifiedById = Auth::user()->UserAccountID;
            $item->DateModified = Carbon::now()->format('Y-m-d H:i:s');
            $item->save();

            // Soft delete inventory record
            $inventory->update([
                'IsDeleted' => true,
                'DeletedById' => Auth::user()->UserAccountID,
                'DateDeleted' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            DB::commit();
            return back()->with('success', 'Inventory record deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Inventory deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete inventory record: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            DB::beginTransaction();

            $inventory = Inventory::findOrFail($id);
            
            // Update item stock
            $item = $inventory->item;
            $item->StocksAvailable += $inventory->StocksAdded;
            $item->ModifiedById = Auth::user()->UserAccountID;
            $item->DateModified = Carbon::now()->format('Y-m-d H:i:s');
            $item->save();

            // Restore inventory record
            $inventory->update([
                'IsDeleted' => false,
                'DeletedById' => null,
                'DateDeleted' => null,
                'ModifiedById' => Auth::user()->UserAccountID,
                'DateModified' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            DB::commit();
            return back()->with('success', 'Inventory record restored successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Inventory restore failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to restore inventory record: ' . $e->getMessage());
        }
    }
}
