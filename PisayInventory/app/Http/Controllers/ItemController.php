<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Classification;
use App\Models\Unit;
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
            // Get items with all relationships
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

            $classifications = Classification::where('IsDeleted', 0)->get();
            $units = Unit::where('IsDeleted', 0)->get();
            $suppliers = Supplier::where('IsDeleted', 0)->get();

            return view('items.index', compact('items', 'classifications', 'units', 'suppliers'));
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
        try {
            DB::beginTransaction();

            // Validate the request
            $validated = $request->validate([
                'ItemName' => 'required|string|max:255',
                'ClassificationId' => 'required|exists:classification,ClassificationId',
                'StocksAvailable' => 'required|integer|min:0', // Initial stock
                'Description' => 'nullable|string'
            ]);

            // Create the item
            $item = new Item();
            $item->ItemName = $validated['ItemName'];
            $item->ClassificationId = $validated['ClassificationId'];
            $item->StocksAvailable = $validated['StocksAvailable'];
            $item->Description = $validated['Description'];
            $item->DateCreated = Carbon::now()->format('Y-m-d H:i:s');
            $item->CreatedById = Auth::user()->UserAccountID;
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
            $oldStock = $item->StocksAvailable;
            
            // Update item
            $item->ItemName = $request->ItemName;
            $item->Description = $request->Description;
            $item->UnitOfMeasureId = $request->UnitOfMeasureId;
            $item->ClassificationId = $request->ClassificationId;
            $item->SupplierID = $request->SupplierID;
            $item->StocksAvailable = $request->StocksAvailable;
            $item->ReorderPoint = $request->ReorderPoint;
            $item->ModifiedById = Auth::id();
            $item->DateModified = now();
            $item->save();

            // Create inventory record if stock changed
            if ($oldStock != $request->StocksAvailable) {
                $item->inventories()->create([
                    'Quantity' => $request->StocksAvailable - $oldStock,
                    'InventoryDate' => now(),
                    'CreatedById' => Auth::id(),
                    'DateCreated' => now(),
                    'IsDeleted' => false
                ]);
            }

            DB::commit();
            return redirect()->route('items.index')->with('success', 'Item updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update item')->withInput();
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