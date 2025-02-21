<?php

namespace App\Http\Controllers;
use App\Models\Item;
use App\Models\Classification;
use App\Models\UnitOfMeasure;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Employee;
use Illuminate\Support\Facades\Storage;
use App\Models\RolePolicy;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ItemsImport;

class ItemController extends Controller
{
    public function index()
    {
        try {
            $userPermissions = $this->getUserPermissions();
            $activeItems = Item::with([
                'classification', 
                'unitOfMeasure', 
                'createdBy'
            ])
            ->where('IsDeleted', false)
            ->paginate(10);

            // Add this debugging
            foreach ($activeItems as $item) {
                Log::info('Item creator details:', [
                    'item_id' => $item->ItemId,
                    'created_by_id' => $item->CreatedById,
                    'creator_info' => $item->createdBy
                ]);
            }

            $deletedItems = Item::with([
                'classification', 
                'unitOfMeasure', 
                'deletedBy'
            ])
            ->where('IsDeleted', true)
            ->paginate(10);

            $classifications = Classification::where('IsDeleted', 0)->get();
            $units = UnitOfMeasure::all();

            return view('items.index', [
                'activeItems' => $activeItems,
                'deletedItems' => $deletedItems,
                'classifications' => $classifications,
                'units' => $units,
                'userPermissions' => $userPermissions
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading items: ' . $e->getMessage());
            return back()->with('error', 'Error loading items: ' . $e->getMessage());
        }
    }


    public function previewColumns(Request $request)
{
    try {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls',
        ]);

        $path = $request->file('excel_file')->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        $columns = [];

        foreach ($worksheet->getRowIterator(1, 1) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                $colName = trim(strtolower($cell->getValue())); // Trim spaces and convert to lowercase
                if (!empty($colName)) {
                    $columns[] = $colName;
                }
            }
        }

        Log::info('Extracted Columns from Excel:', $columns);
        return response()->json(['columns' => $columns]);

    } catch (\Exception $e) {
        Log::error('Error previewing Excel columns: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


    public function import(Request $request)
{
    try {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls',
            'column_mapping' => 'required|array',
            'column_mapping.ItemName' => 'required|string',  // Changed this line
        ]);

        // Add debug logging
        Log::info('Import Request Data:', [
            'column_mapping' => $request->column_mapping,
            'default_values' => [
                'classification' => $request->default_classification,
                'unit' => $request->default_unit,
                'stocks' => $request->default_stocks,
                'reorder_point' => $request->default_reorder_point
            ]
        ]);

        DB::beginTransaction();

        $import = new ItemsImport(
            $request->column_mapping,
            $request->default_classification,
            $request->default_unit,
            $request->default_stocks ?? 0,
            $request->default_reorder_point ?? 0,
            Auth::id()
        );

        Excel::import($import, $request->file('excel_file'));
        
        DB::commit();
        return redirect()->route('items.index')->with('success', 'Items imported successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error importing items:', [
            'error' => $e->getMessage(),
            'column_mapping' => $request->column_mapping ?? null
        ]);
        return redirect()->back()->with('error', $e->getMessage());
    }
}

    public function create()
    {
        $units = UnitOfMeasure::all();
        $classifications = Classification::all();
        return view('items.create', compact('units', 'classifications'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            // Add this logging to check the values
            Log::info('Creating item with employee details:', [
                'employee_id' => $currentEmployee->EmployeeID,
                'user_account_id' => $currentEmployee->UserAccountID,
                'auth_user_id' => Auth::id(),
                'auth_user' => Auth::user()
            ]);

            // Validate the request
            $validated = $request->validate([
                'ItemName' => 'required|string|max:255',
                'ClassificationId' => 'required|exists:classification,ClassificationId',
                'UnitOfMeasureId' => 'required|exists:unitofmeasure,UnitOfMeasureId',
                'ReorderPoint' => 'required|integer|min:0',
                'Description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Create the item
            $item = new Item();
            $item->ItemName = $validated['ItemName'];
            $item->Description = $validated['Description'];
            $item->UnitOfMeasureId = $validated['UnitOfMeasureId'];
            $item->ClassificationId = $validated['ClassificationId'];
            $item->StocksAvailable = 0;
            $item->ReorderPoint = $validated['ReorderPoint'];
            $item->CreatedById = Auth::id();
            $item->DateCreated = now();
            $item->IsDeleted = false;

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('items', 'public');
                $item->ImagePath = $imagePath;
            }

            $item->save();

            // Add this logging to verify saved data
            Log::info('Item created with details:', [
                'item_id' => $item->ItemId,
                'created_by_id' => $item->CreatedById,
                'auth_id' => Auth::id()
            ]);

            DB::commit();
            
            // Return only one success message
            return redirect()->route('items.index')
                ->with('success', 'Item created successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating item: ' . $e->getMessage());
            return back()->with('error', 'Error creating item: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'ItemName' => 'required|string|max:255',
                'UnitOfMeasureId' => 'required|exists:unitofmeasure,UnitOfMeasureId',
                'ClassificationId' => 'required|exists:classification,ClassificationId',
                'ReorderPoint' => 'required|integer|min:0',
                'Description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            DB::beginTransaction();

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            $item = Item::findOrFail($id);
            
            // Update item details
            $item->ItemName = $request->ItemName;
            $item->Description = $request->Description;
            $item->UnitOfMeasureId = $request->UnitOfMeasureId;
            $item->ClassificationId = $request->ClassificationId;
            $item->ReorderPoint = $request->ReorderPoint;
            $item->ModifiedById = $currentEmployee->EmployeeID;
            $item->DateModified = now();

            // Handle image upload if new image is provided
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($item->ImagePath) {
                    Storage::disk('public')->delete($item->ImagePath);
                }
                $imagePath = $request->file('image')->store('items', 'public');
                $item->ImagePath = $imagePath;
            }

            $item->save();

            DB::commit();
            return redirect()->route('items.index')
                ->with('success', 'Item updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating item: ' . $e->getMessage());
            return back()->with('error', 'Error updating item: ' . $e->getMessage())
                ->withInput();
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
            $inventory = new Inventory();
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
            $inventory = new Inventory();
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
            
            // Enhanced debug logging
            Log::info('Delete attempt details:', [
                'received_id' => $id,
                'id_type' => gettype($id),
                'request_item_id' => request('item_id')
            ]);
            
            // Try to get ID from different sources
            $itemId = is_numeric($id) ? $id : request('item_id');
            
            if (!is_numeric($itemId)) {
                Log::error('Invalid item ID received:', ['id' => $itemId]);
                throw new \Exception('Invalid item ID');
            }
    
            $item = Item::find($itemId);
            if (!$item) {
                Log::error('Item not found:', ['id' => $itemId]);
                throw new \Exception('Item not found');
            }
    
            // Log the item being deleted
            Log::info('Found item to delete:', [
                'item_id' => $item->ItemId,
                'item_name' => $item->ItemName
            ]);
    
            $item->update([
                'IsDeleted' => true,
                'DeletedById' => Auth::id(),
                'DateDeleted' => now()
            ]);
    
            DB::commit();
            return redirect()->route('items.index')->with('success', 'Item deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Item deletion failed:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error deleting item: ' . $e->getMessage());
        }
    }



    public function restore($id)
    {
        try {
            DB::beginTransaction();

            $item = Item::findOrFail($id);
            
            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            $item->update([
                'IsDeleted' => false,
                'RestoredById' => $currentEmployee->EmployeeID,
                'DateRestored' => now(),
                'DeletedById' => null,
                'DateDeleted' => null
            ]);

            DB::commit();
            return redirect()->route('items.index')->with('success', 'Item restored successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restoring item: ' . $e->getMessage());
            return back()->with('error', 'Error restoring item: ' . $e->getMessage());
        }
    }

    public function manage()
    {
        try {
            $items = Item::with([
                'classification' => function($query) {
                    $query->where('IsDeleted', 0);
                },
                'unitOfMeasure' => function($query) {
                    $query->where('IsDeleted', 0);
                }
            ])
            ->where('IsDeleted', 0)
            ->get();
            
            $classifications = Classification::where('IsDeleted', 0)->get();
            $units = UnitOfMeasure::where('IsDeleted', 0)->get();

            Log::info('Items loaded:', [
                'items_count' => $items->count(),
            ]);

            return view('items.manage', compact('items', 'classifications', 'units'));
        } catch (\Exception $e) {
            Log::error('Error in ItemController@manage: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'Error loading items management page');
        }
    }

    public function edit(Item $item)
    {
        // Remove suppliers
        $classifications = Classification::where('IsDeleted', false)->get();
        $units = UnitOfMeasure::all();

        return view('items.edit', compact('item', 'classifications', 'units'));
    }

    private function getUserPermissions()
    {
        $userRole = Auth::user()->role;
        return RolePolicy::whereHas('role', function($query) use ($userRole) {
            $query->where('RoleName', $userRole);
        })->where('Module', 'Items')->first();
    }
} 