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
use Illuminate\Validation\ValidationException;

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

            // Find the item first
            $item = Item::findOrFail($request->ItemId);
            $quantity = abs((int)$request->StocksAdded);

            // Create new inventory record
            $inventory = new Inventory();
            $inventory->ItemId = $request->ItemId;
            $inventory->ClassificationId = $item->ClassificationId;
            $inventory->IsDeleted = false;
            $inventory->DateCreated = now();
            $inventory->CreatedById = Auth::id();
            $inventory->ModifiedById = Auth::id();
            $inventory->DateModified = now();

            // Handle stock in/out based on type
            if ($request->type === 'in') {
                $inventory->StocksAdded = $quantity;
                $inventory->StockOut = 0;
                $inventory->StocksAvailable = $item->StocksAvailable + $quantity; // Update with new total
                
                // Update item's total stock
                $item->StocksAvailable += $quantity;
            } else {
                // For stock out
                if ($quantity > $item->StocksAvailable) {
                    throw new \Exception("Not enough stocks available! Current stock: {$item->StocksAvailable}");
                }
                
                $inventory->StocksAdded = 0;
                $inventory->StockOut = $quantity;
                $inventory->StocksAvailable = $item->StocksAvailable - $quantity; // Subtract from current total
                
                // Update item's total stock
                $item->StocksAvailable -= $quantity;
            }

            \Log::info('Saving with data:', [
                'type' => $request->type,
                'quantity' => $quantity,
                'old_stock' => $item->getOriginal('StocksAvailable'),
                'new_stock' => $item->StocksAvailable,
                'inventory_available' => $inventory->StocksAvailable
            ]);

            $inventory->save();
            $item->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventory ' . ($request->type === 'in' ? 'added' : 'removed') . ' successfully',
                'data' => [
                    'InventoryID' => $inventory->InventoryID,
                    'ItemName' => $item->ItemName,
                    'ClassificationName' => $item->classification->ClassificationName ?? 'N/A',
                    'StocksAdded' => $inventory->StocksAdded,
                    'StockOut' => $inventory->StockOut,
                    'StocksAvailable' => $inventory->StocksAvailable,
                    'ItemStocksAvailable' => $item->StocksAvailable,
                    'CreatedBy' => $inventory->created_by_user->Username ?? 'N/A',
                    'DateCreated' => $inventory->DateCreated ? date('Y-m-d H:i', strtotime($inventory->DateCreated)) : 'N/A'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in store:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inventory: ' . $e->getMessage()
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
            $item = Item::findOrFail($inventory->ItemId);

            // Calculate the difference in stocks
            $oldStocksAdded = $inventory->StocksAdded;
            $oldStockOut = $inventory->StockOut;
            
            // Update inventory record
            $inventory->StocksAdded = $request->StocksAdded ?? $oldStocksAdded;
            $inventory->StockOut = $request->StockOut ?? $oldStockOut;
            
            // Calculate new available stocks
            $stocksDifference = ($inventory->StocksAdded - $oldStocksAdded) - ($inventory->StockOut - $oldStockOut);
            $inventory->StocksAvailable = $item->StocksAvailable + $stocksDifference;
            
            $inventory->ModifiedById = Auth::id();
            $inventory->DateModified = now();
            $inventory->save();

            // Update item's stock
            $item->StocksAvailable += $stocksDifference;
            $item->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventory updated successfully',
                'data' => [
                    'StocksAdded' => $inventory->StocksAdded,
                    'StockOut' => $inventory->StockOut,
                    'StocksAvailable' => $inventory->StocksAvailable
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inventory: ' . $e->getMessage()
            ], 500);
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
