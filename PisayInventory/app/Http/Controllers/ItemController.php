<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Classification;
use App\Models\UnitOfMeasure;
use App\Models\Supplier;
use App\Models\Inventory;
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
            ->paginate(10);

            // Get trashed items with relationships
            $trashedItems = Item::with([
                'classification',
                'unitOfMeasure',
                'supplier',
                'deleted_by_user'
            ])
            ->where('IsDeleted', 1)
            ->orderBy('ItemName')
            ->paginate(10);

            $classifications = Classification::where('IsDeleted', 0)->get();
            $units = UnitOfMeasure::all();
            $suppliers = Supplier::where('IsDeleted', 0)->get();

            return view('items.index', compact('items', 'trashedItems', 'classifications', 'units', 'suppliers'));
        } catch (\Exception $e) {
            \Log::error('Error in ItemController@index: ' . $e->getMessage());
            return back()->with('error', 'Unable to load items. Error: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $units = UnitOfMeasure::all();
        $suppliers = Supplier::all();
        $classifications = Classification::all();
        return view('items.create', compact('units', 'suppliers', 'classifications'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate the request
            $validated = $request->validate([
                'ItemName' => 'required|string|max:255',
                'ClassificationId' => 'required|exists:classification,ClassificationId',
                'UnitOfMeasureId' => 'required|exists:UnitOfMeasure,UnitOfMeasureId',
                'SupplierID' => 'required|exists:suppliers,SupplierID',
                'StocksAvailable' => 'required|integer|min:0',
                'ReorderPoint' => 'required|integer|min:0',
                'Description' => 'nullable|string',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('items', 'public');
            }

            // Create the item
            $item = new Item();
            $item->ItemName = $validated['ItemName'];
            $item->ClassificationId = $validated['ClassificationId'];
            $item->UnitOfMeasureId = $validated['UnitOfMeasureId'];
            $item->SupplierID = $validated['SupplierID'];
            $item->StocksAvailable = $validated['StocksAvailable'];
            $item->ReorderPoint = $validated['ReorderPoint'];
            $item->Description = $validated['Description'];
            $item->ImagePath = $imagePath ?? null;
            $item->DateCreated = Carbon::now()->format('Y-m-d H:i:s');
            
            // Debug user info
            \Log::info('User info:', [
                'Auth::id()' => Auth::id(),
                'Auth::user()' => Auth::user(),
                'UserAccountID' => Auth::user()->UserAccountID ?? 'null'
            ]);
            
            $item->CreatedById = Auth::user()->UserAccountID;
            $item->ModifiedById = Auth::user()->UserAccountID;
            $item->DateModified = Carbon::now()->format('Y-m-d H:i:s');
            $item->IsDeleted = false;
            $item->save();

            // Create initial inventory record if there's initial stock
            if ($validated['StocksAvailable'] > 0) {
                $inventory = new Inventory();
                $inventory->ItemId = $item->ItemId;
                $inventory->ClassificationId = $validated['ClassificationId'];
                $inventory->StocksAdded = $validated['StocksAvailable'];
                $inventory->StockOut = 0;
                $inventory->StocksAvailable = $validated['StocksAvailable'];
                $inventory->DateCreated = Carbon::now()->format('Y-m-d H:i:s');
                $inventory->CreatedById = Auth::user()->UserAccountID;
                $inventory->ModifiedById = Auth::user()->UserAccountID;
                $inventory->DateModified = Carbon::now()->format('Y-m-d H:i:s');
                $inventory->IsDeleted = false;
                $inventory->save();
            }

            DB::commit();
            return redirect()->route('items.index')
                ->with('success', 'Item created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Item creation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to create item: ' . $e->getMessage())
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
            $item->ModifiedById = Auth::user()->UserAccountID;
            $item->DateModified = Carbon::now()->format('Y-m-d H:i:s');
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
            $inventory->ModifiedById = Auth::id();
            $inventory->DateModified = now();
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
            $inventory->ModifiedById = Auth::id();
            $inventory->DateModified = now();
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
            $units = UnitOfMeasure::where('IsDeleted', 0)->get();
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