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
use Illuminate\Support\Facades\Log;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Inventory::with([
            'item.classification',
            'created_by_user',
            'modified_by_user',
            'deleted_by_user'
        ])
            ->select('inventory.*', 'items.StocksAvailable as ItemStocksAvailable')
            ->join('items', 'inventory.ItemId', '=', 'items.ItemId')
            ->where('items.IsDeleted', 0);

        // Only show active records unless show_deleted is requested
        if (!$request->has('show_deleted')) {
            $query->where('inventory.IsDeleted', 0);
        }

        $inventories = $query->latest('inventory.DateCreated')
            ->paginate(10)
            ->withQueryString();

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

            $inventory = new Inventory();
            $inventory->ItemId = $request->ItemId;
            $inventory->ClassificationId = $request->ClassificationId;
            $inventory->IsDeleted = false;
            $inventory->DateCreated = now();
            $inventory->CreatedById = Auth::id();
            $inventory->ModifiedById = Auth::id();
            $inventory->DateModified = now();
            $inventory->StocksAdded = $request->StocksAdded;
            $inventory->StocksAvailable = $request->StocksAdded;
            $inventory->save();

            // Get related data for response
            $item = Item::find($request->ItemId);
            $classification = Classification::find($request->ClassificationId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventory added successfully',
                'data' => [
                    'InventoryID' => $inventory->InventoryID,
                    'ItemName' => $item->ItemName,
                    'ClassificationName' => $classification->ClassificationName,
                    'StocksAdded' => $inventory->StocksAdded,
                    'StocksAvailable' => $inventory->StocksAvailable
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add inventory: ' . $e->getMessage()
            ], 500);
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
            $item = $inventory->item;

            // Calculate the difference in stock
            $oldStockAdded = $inventory->StocksAdded;
            $newStockAdded = (int)$request->StocksAdded;
            $stockDifference = $newStockAdded - $oldStockAdded;

            // Update item's stock
            $newItemStock = $item->StocksAvailable + $stockDifference;
            if ($newItemStock < 0) {
                throw new \Exception("Cannot update: Would result in negative stock.");
            }

            // Update item
            $item->StocksAvailable = $newItemStock;
            $item->ModifiedById = Auth::user()->UserAccountID;
            $item->DateModified = Carbon::now()->format('Y-m-d H:i:s');
            $item->save();

            // Update inventory record
            $inventory->StocksAdded = $newStockAdded;
            $inventory->StocksAvailable = $newItemStock;
            $inventory->ModifiedById = Auth::user()->UserAccountID;
            $inventory->DateModified = Carbon::now()->format('Y-m-d H:i:s');
            $inventory->save();

            DB::commit();
            return redirect()->route('inventory.index')
                ->with('success', 'Inventory record updated successfully. New stock: ' . $newItemStock);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update inventory: ' . $e->getMessage())
                        ->withInput();
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
            
            // Soft delete
            $inventory->IsDeleted = true;
            $inventory->DeletedById = Auth::user()->UserAccountID;
            $inventory->DateDeleted = Carbon::now()->format('Y-m-d H:i:s');
            $inventory->save();

            DB::commit();
            return redirect()->route('inventory.index')
                ->with('success', 'Inventory record deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete inventory record: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            DB::beginTransaction();

            $inventory = Inventory::findOrFail($id);
            
            // Restore
            $inventory->IsDeleted = false;
            $inventory->DeletedById = null;
            $inventory->DateDeleted = null;
            $inventory->ModifiedById = Auth::user()->UserAccountID;
            $inventory->DateModified = Carbon::now()->format('Y-m-d H:i:s');
            $inventory->save();

            DB::commit();
            return redirect()->route('inventory.index')
                ->with('success', 'Inventory record restored successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory restore failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to restore inventory record: ' . $e->getMessage());
        }
    }
}
