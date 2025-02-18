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
use App\Models\Employee;
use Illuminate\Support\Facades\Storage;
use App\Models\RolePolicy;

class ItemController extends Controller
{
    public function index()
    {
        try {
            $userPermissions = $this->getUserPermissions();
            $activeItems = Item::with([
                'classification', 
                'unitOfMeasure', 
                'supplier', 
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
                'supplier', 
                'deletedBy'
            ])
            ->where('IsDeleted', true)
            ->paginate(10);

            $classifications = Classification::where('IsDeleted', 0)->get();
            $units = UnitOfMeasure::all();
            $suppliers = Supplier::where('IsDeleted', false)->get();

            return view('items.index', [
                'activeItems' => $activeItems,
                'deletedItems' => $deletedItems,
                'classifications' => $classifications,
                'units' => $units,
                'suppliers' => $suppliers,
                'userPermissions' => $userPermissions
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading items: ' . $e->getMessage());
            return back()->with('error', 'Error loading items: ' . $e->getMessage());
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
                'SupplierID' => 'required|exists:suppliers,SupplierID',
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
            $item->SupplierID = $validated['SupplierID'];
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
                'SupplierID' => 'required|exists:suppliers,SupplierID',
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
            $item->SupplierID = $request->SupplierID;
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

    public function edit(Item $item)
    {
        // Get related data for dropdowns
        $suppliers = Supplier::where('IsDeleted', false)->get();
        $classifications = Classification::where('IsDeleted', false)->get();
        $units = UnitOfMeasure::all();

        return view('items.edit', compact('item', 'suppliers', 'classifications', 'units'));
    }

    private function getUserPermissions()
    {
        $userRole = auth()->user()->role;
        return RolePolicy::whereHas('role', function($query) use ($userRole) {
            $query->where('RoleName', $userRole);
        })->where('Module', 'Items')->first();
    }
} 