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
use App\Exports\ItemsExport;

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
            'column_mapping.ItemName' => 'required|string',
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
        
        $skippedRows = $import->getSkippedRows();
        $successCount = $import->getSuccessCount(); // Add this method to your ItemsImport class
                
        DB::commit();

        // Prepare the response message
        $message = "Successfully imported {$successCount} items.";
        
        if (!empty($skippedRows)) {
            $message .= "\n\nSkipped " . count($skippedRows) . " duplicate items.";
        }

        return response()->json([
            'success' => true,
            'import_result' => [
                'message' => $message,
                'skipped' => $skippedRows
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => $e->getMessage()
        ], 422);
    }
}

public function export(Request $request)
{
    try {
        $request->validate([
            'fields' => 'required|array',
            'fields.*' => 'required|string|in:ItemName,Description,Classification,Unit,StocksAvailable,ReorderPoint',
            'format' => 'required|in:xlsx,csv',
            'items_status' => 'required|in:active,deleted,all'
        ]);

        // Add this for debugging
        Log::info('Export request:', [
            'fields' => $request->fields,
            'format' => $request->format,
            'items_status' => $request->items_status
        ]);

        $fileName = 'items_' . date('Y-m-d_His') . '.' . $request->format;
        
        return Excel::download(
            new ItemsExport($request->fields, $request->items_status),
            $fileName
        );
    } catch (\Exception $e) {
        Log::error('Error exporting items: ' . $e->getMessage());
        return back()->with('error', 'Error exporting items: ' . $e->getMessage());
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

                    // Check for duplicates first
        $duplicate = Item::checkDuplicate($request->ItemName, $request->Description)->first();
        if ($duplicate) {
            throw new \Exception('An item with the same name and description already exists.');
        }

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
                // Delete old image
                if ($item->ImagePath) {
                    Storage::delete('public/' . $item->ImagePath);
                }
                
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('public/items', $filename);
                
                // Add debug logging
                Log::info('Stored image file:', [
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_filename' => $filename,
                    'full_path' => $path,
                    'stripped_path' => str_replace('public/', '', $path)
                ]);
                
                $item->ImagePath = str_replace('public/', '', $path); // Store relative path
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
            // Debug logging for incoming ID
            Log::info('Update item request:', [
                'raw_id' => $id,
                'id_type' => gettype($id),
                'request_data' => $request->all()
            ]);

            // Clean and validate the ID
            $cleanId = is_numeric($id) ? $id : null;
            
            if (!$cleanId) {
                // Try to extract ID if it's an object or array
                if (is_object($id) && isset($id->ItemId)) {
                    $cleanId = $id->ItemId;
                } elseif (is_array($id) && isset($id['ItemId'])) {
                    $cleanId = $id['ItemId'];
                }
            }

            // Log the cleaned ID
            Log::info('Cleaned item ID:', ['clean_id' => $cleanId]);

            // Validate request
            $validated = $request->validate([
                'ItemName' => 'required|string|max:255',
                'UnitOfMeasureId' => 'required|exists:unitofmeasure,UnitOfMeasureId',
                'ClassificationId' => 'required|exists:classification,ClassificationId',
                'ReorderPoint' => 'required|integer|min:0',
                'Description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            DB::beginTransaction();

            // Find the item with detailed logging
            $item = Item::where('ItemId', $cleanId)
                       ->where('IsDeleted', false)
                       ->first();

            // Log item lookup result
            Log::info('Item lookup result:', [
                'clean_id' => $cleanId,
                'item_found' => $item ? true : false,
                'item_details' => $item ? [
                    'ItemId' => $item->ItemId,
                    'ItemName' => $item->ItemName
                ] : null
            ]);
            
            if (!$item) {
                throw new \Exception("Item not found with ID: $cleanId");
            }

            // Check for duplicates, excluding the current item
            $duplicate = Item::checkDuplicate($validated['ItemName'], $validated['Description'])
                ->where('ItemId', '!=', $cleanId)
                ->first();

            if ($duplicate) {
                throw new \Exception('An item with the same name and description already exists.');
            }

            // Get current employee
            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();
            
            // Update item details
            $item->ItemName = $validated['ItemName'];
            $item->Description = $validated['Description'];
            $item->UnitOfMeasureId = $validated['UnitOfMeasureId'];
            $item->ClassificationId = $validated['ClassificationId'];
            $item->ReorderPoint = $validated['ReorderPoint'];
            $item->ModifiedById = $currentEmployee->EmployeeID;
            $item->DateModified = now();

            // Handle image upload if new image is provided
            if ($request->hasFile('image')) {
                // Delete old image
                if ($item->ImagePath) {
                    Storage::delete('public/' . $item->ImagePath);
                }
                
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('public/items', $filename);
                
                // Add debug logging
                Log::info('Stored image file:', [
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_filename' => $filename,
                    'full_path' => $path,
                    'stripped_path' => str_replace('public/', '', $path)
                ]);
                
                $item->ImagePath = str_replace('public/', '', $path); // Store relative path
            }

            // Save the changes
            $item->save();

            DB::commit();

            // Log successful update
            Log::info('Item updated successfully:', [
                'item_id' => $item->ItemId,
                'modified_by' => $currentEmployee->EmployeeID,
                'updated_values' => $validated
            ]);

            return redirect()->route('items.index')
                ->with('success', 'Item updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating item:', [
                'id' => $id,
                'clean_id' => $cleanId ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->withInput()
                ->with('error', 'Error updating item: ' . $e->getMessage());
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Check permissions
            $userPermissions = $this->getUserPermissions();
            if (!$userPermissions || !$userPermissions->CanDelete) {
                return redirect()->back()->with('error', 'You do not have permission to delete items.');
            }

            DB::beginTransaction();
            
            // Find the item
            $item = Item::findOrFail($id);
            
            // Check for dependencies in the correct table
            // Instead of checking 'order_items', check if item is used in POS orders
            $hasOrders = false;
            
            // Check if we have the pos_order_items table
            try {
                $hasOrders = DB::table('pos_order_items')
                    ->where('menu_item_id', $id)
                    ->exists();
            } catch (\Exception $e) {
                // Table might not exist, log it but continue
                Log::warning('Could not check pos_order_items table: ' . $e->getMessage());
            }
            
            // If the first check didn't work, try another possible table structure
            if (!$hasOrders) {
                try {
                    $hasOrders = DB::table('menu_item_order')
                        ->where('menu_item_id', $id)
                        ->exists();
                } catch (\Exception $e) {
                    // Table might not exist, log it but continue
                    Log::warning('Could not check menu_item_order table: ' . $e->getMessage());
                }
            }
            
            // If neither table exists, just do a soft delete to be safe
            if ($hasOrders) {
                // Soft delete if used in orders
                $item->update([
                    'IsDeleted' => true,
                    'DeletedById' => Auth::id(),
                    'DateDeleted' => now()
                ]);
            } else {
                // Just do soft delete for safety
                $item->update([
                    'IsDeleted' => true,
                    'DeletedById' => Auth::id(),
                    'DateDeleted' => now()
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('items.index')
                ->with('success', 'Item deleted successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in item delete: ' . $e->getMessage());
            
            return redirect()->route('items.index')
                ->with('error', 'Failed to delete item: ' . $e->getMessage());
        }
    }



    public function restore($id)
    {
        try {
            // Add detailed logging
            Log::info('Starting item restore process', [
                'item_id' => $id,
                'user' => Auth::user()->UserAccountID ?? 'Unknown'
            ]);
            
            DB::beginTransaction();

            // Find the item, using findOrFail to throw an exception if not found
            $item = Item::findOrFail($id);
            Log::info('Item found for restore', [
                'item_id' => $item->ItemId,
                'item_name' => $item->ItemName
            ]);
            
            // Get current employee
            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->first();
                
            if (!$currentEmployee) {
                Log::error('Failed to find employee record for current user', [
                    'user_id' => Auth::user()->UserAccountID ?? 'Unknown'
                ]);
                throw new \Exception('Employee record not found for the current user.');
            }
                
            Log::info('Found employee for restore operation', [
                'employee_id' => $currentEmployee->EmployeeID,
                'user_account_id' => $currentEmployee->UserAccountID
            ]);

            // Update the item to restore it
            $item->update([
                'IsDeleted' => false,
                'RestoredById' => $currentEmployee->EmployeeID,
                'DateRestored' => now(),
                'DeletedById' => null,
                'DateDeleted' => null
            ]);
            
            Log::info('Item updated successfully', [
                'item_id' => $item->ItemId,
                'restored_by' => $currentEmployee->EmployeeID
            ]);

            DB::commit();
            Log::info('Item restore transaction committed');
            
            return redirect()->route('items.index')->with('success', 'Item restored successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restoring item', [
                'item_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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

    public function getUserPermissions($module = null)
    {
        return parent::getUserPermissions('Items');
    }

    public function search(Request $request)
    {
        try {
            $query = Item::query()
                ->where('IsDeleted', false);

            if ($request->has('q') && !empty($request->q)) {
                $query->where('ItemName', 'like', '%' . $request->q . '%');
            }

            $items = $query->get();

            $formattedItems = $items->map(function($item) {
                return [
                    'id' => $item->ItemId,
                    'text' => $item->ItemName
                ];
            });

            return response()->json($formattedItems);
        } catch (\Exception $e) {
            \Log::error('Item search error: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
} 