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