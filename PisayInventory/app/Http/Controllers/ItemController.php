<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Classification;
use App\Models\Unit;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    public function index()
    {
        try {
            // Get active items with all relationships
            $items = Item::with([
                'classification',
                'unitOfMeasure',
                'supplier',
                'created_by_user',
                'modified_by_user'
            ])
            ->where('IsDeleted', 0)
            ->orderBy('ItemName')
            ->get();

            // Get trashed items with relationships
            $trashedItems = Item::with([
                'classification',
                'unitOfMeasure',
                'supplier',
                'deleted_by_user'
            ])
            ->where('IsDeleted', 1)
            ->orderBy('ItemName')
            ->get();

            $classifications = Classification::where('IsDeleted', 0)->get();
            $units = Unit::where('IsDeleted', 0)->get();
            $suppliers = Supplier::where('IsDeleted', 0)->get();

            return view('items.index', compact('items', 'trashedItems', 'classifications', 'units', 'suppliers'));
        } catch (\Exception $e) {
            \Log::error('Error in ItemController@index: ' . $e->getMessage());
            return back()->with('error', 'Unable to load items. Error: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $units = Unit::all();
        $suppliers = Supplier::all();
        $classifications = Classification::all();
        return view('items.create', compact('units', 'suppliers', 'classifications'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'ItemName' => 'required|string|max:255',
                'Description' => 'nullable|string',
                'ClassificationId' => 'required|exists:classification,ClassificationId',
                'UnitOfMeasureId' => 'required|exists:unitofmeasure,UnitOfMeasureId',
                'SupplierID' => 'required|exists:suppliers,SupplierID',
                'StocksAvailable' => 'required|integer|min:0',
                'ReorderPoint' => 'required|integer|min:0'
            ]);

            // Check if classification exists and is active
            $classification = Classification::where('ClassificationId', $validated['ClassificationId'])
                ->where('IsDeleted', 0)
                ->first();

            if (!$classification) {
                throw new \Exception('Selected classification is not valid or has been deleted.');
            }

            $item = Item::create([
                'ItemName' => $validated['ItemName'],
                'Description' => $validated['Description'],
                'ClassificationId' => $validated['ClassificationId'],
                'UnitOfMeasureId' => $validated['UnitOfMeasureId'],
                'SupplierID' => $validated['SupplierID'],
                'StocksAvailable' => $validated['StocksAvailable'],
                'ReorderPoint' => $validated['ReorderPoint'],
                'CreatedById' => Auth::user()->UserAccountID,
                'DateCreated' => Carbon::now()->format('Y-m-d H:i:s'),
                'IsDeleted' => false
            ]);

            DB::commit();
            return redirect()->route('items.index')->with('success', 'Item created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Item creation failed: ' . $e->getMessage());
            return back()->with('error', 'Error creating item: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $item = Item::findOrFail($id);
            
            // Update item details
            $item->ItemName = $request->ItemName;
            $item->Description = $request->Description;
            $item->UnitOfMeasureId = $request->UnitOfMeasureId;
            $item->ClassificationId = $request->ClassificationId;
            $item->SupplierID = $request->SupplierID;
            $item->ReorderPoint = $request->ReorderPoint;
            $item->ModifiedById = Auth::id();
            $item->DateModified = now();
            $item->save();

            DB::commit();
            return redirect()->route('items.index')->with('success', 'Item updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update item')->withInput();
        }
    }

    public function stockIn(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $item = Item::findOrFail($id);
            $quantity = (int)$request->quantity;

            if ($quantity <= 0) {
                throw new \Exception('Quantity must be greater than 0');
            }

            // Calculate new stock by adding the quantity
            $newStock = $item->StocksAvailable + $quantity;

            // Update item's stock first
            $item->StocksAvailable = $newStock;
            $item->ModifiedById = Auth::id();
            $item->DateModified = now();
            $item->save();

            // Create inventory record for stock in
            $inventory = new \App\Models\Inventory();
            $inventory->ItemId = $item->ItemId;
            $inventory->ClassificationId = $item->ClassificationId;
            $inventory->IsDeleted = false;
            $inventory->DateCreated = now();
            $inventory->CreatedById = Auth::id();
            $inventory->StocksAdded = $quantity;
            $inventory->StocksAvailable = $newStock;
            $inventory->save();

            DB::commit();
            return redirect()->route('items.index')->with('success', "Successfully added $quantity items. New stock: $newStock");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock in failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update inventory: ' . $e->getMessage());
        }
    }

    public function stockOut(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $item = Item::findOrFail($id);
            $quantity = (int)$request->quantity;

            if ($quantity <= 0) {
                throw new \Exception('Quantity must be greater than 0');
            }

            // Get current stock from items table
            $currentStock = $item->StocksAvailable;

            if ($quantity > $currentStock) {
                throw new \Exception("Not enough stock available. Current stock: $currentStock");
            }

            // Calculate new stock
            $newStock = $currentStock - $quantity;
            
            // Update item's stock first
            $item->StocksAvailable = $newStock;
            $item->ModifiedById = Auth::id();
            $item->DateModified = now();
            $item->save();

            // Create inventory record for stock out
            $inventory = new \App\Models\Inventory();
            $inventory->ItemId = $item->ItemId;
            $inventory->ClassificationId = $item->ClassificationId;
            $inventory->IsDeleted = false;
            $inventory->DateCreated = now();
            $inventory->CreatedById = Auth::id();
            $inventory->StocksAdded = -$quantity; // Negative for stock out
            $inventory->StocksAvailable = $newStock;
            $inventory->save();

            DB::commit();
            return redirect()->route('items.index')->with('success', "Successfully removed $quantity items. New stock: $newStock");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock out failed: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $item = Item::findOrFail($id);
            $item->update([
                'IsDeleted' => true,
                'DeletedById' => Auth::id(),
                'DateDeleted' => Carbon::now()
            ]);

            DB::commit();
            return redirect()->route('items.index')->with('success', 'Item deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Item deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Error deleting item: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            DB::beginTransaction();
            
            $item = Item::findOrFail($id);
            $item->update([
                'IsDeleted' => false,
                'DeletedById' => null,
                'DateDeleted' => null,
                'ModifiedById' => Auth::id(),
                'DateModified' => Carbon::now()
            ]);

            DB::commit();
            return redirect()->route('items.index')->with('success', 'Item restored successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Item restoration failed: ' . $e->getMessage());
            return back()->with('error', 'Error restoring item: ' . $e->getMessage());
        }
    }

    public function manage()
    {
        try {
            // Eager load relationships with correct names
            $items = Item::with([
                'classification' => function($query) {
                    $query->where('IsDeleted', 0);
                },
                'unitOfMeasure' => function($query) {
                    $query->where('IsDeleted', 0);
                },
                'supplier' => function($query) {
                    $query->where('IsDeleted', 0);
                }
            ])
            ->where('IsDeleted', 0)
            ->get();
            
            $classifications = Classification::where('IsDeleted', 0)->get();
            $units = Unit::where('IsDeleted', 0)->get();
            $suppliers = Supplier::where('IsDeleted', 0)->get();

            Log::info('Items loaded:', [
                'items_count' => $items->count(),
                'items_with_null_supplier' => $items->whereNull('supplier')->count(),
                'suppliers_count' => $suppliers->count()
            ]);

            return view('items.manage', compact('items', 'classifications', 'units', 'suppliers'));
        } catch (\Exception $e) {
            Log::error('Error in ItemController@manage: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'Error loading items management page');
        }
    }
} 