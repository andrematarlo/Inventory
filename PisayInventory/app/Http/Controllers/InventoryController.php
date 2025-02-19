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
use App\Models\RolePolicy;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Debug user and permissions
        \Log::info('User details:', [
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->role ?? 'No role',
            'is_authenticated' => auth()->check()
        ]);

        $userPermissions = null;
        if (auth()->check() && auth()->user()->role) {
            $userPermissions = RolePolicy::where('RoleId', auth()->user()->role)
                ->where('Module', 'LIKE', 'inventory')
                ->where('IsDeleted', 0)
                ->first();
            
            // Debug permissions
            \Log::info('Permissions loaded:', [
                'permissions' => $userPermissions ? 'Has permissions' : 'No permissions',
                'role_id' => auth()->user()->role,
                'can_delete' => $userPermissions?->CanDelete ?? false
            ]);
        }

        // Get all inventory items
        $query = Inventory::with(['item.classification', 'created_by_user', 'modified_by_user', 'deleted_by_user']);

        // Show deleted records if requested
        if ($request->has('show_deleted')) {
            $query->where('IsDeleted', true);
        } else {
            $query->where('IsDeleted', false);
        }

        $inventories = $query->orderBy('DateCreated', 'desc')->paginate(10);

        return view('inventory.index', [
            'inventories' => $inventories,
            'userPermissions' => $userPermissions
        ]);
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

            $currentDateTime = now();
            
            // Find the item first
            $item = Item::findOrFail($request->ItemId);
            $quantity = abs((int)$request->StocksAdded);

            // Create new inventory record
            $inventory = new Inventory();
            $inventory->ItemId = $request->ItemId;
            $inventory->ClassificationId = $item->ClassificationId;
            $inventory->IsDeleted = false;
            $inventory->DateCreated = $currentDateTime;
            $inventory->CreatedById = Auth::id();
            $inventory->ModifiedById = Auth::id();
            $inventory->DateModified = $currentDateTime;

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

            // Reload the inventory to get fresh data
            $inventory = $inventory->fresh();

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
                    'DateCreated' => date('Y-m-d h:i:s A', strtotime($inventory->DateCreated)),
                    'DateModified' => date('Y-m-d h:i:s A', strtotime($inventory->DateModified))
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
            
            $quantity = abs((int)$request->StocksAdded);
            $type = $request->type;

            if ($type === 'out' && $quantity > $item->StocksAvailable) {
                throw new \Exception("Cannot stock out more than available quantity. Current stock: {$item->StocksAvailable}");
            }

            // Create new inventory record
            $newInventory = new Inventory();
            $newInventory->ItemId = $inventory->ItemId;
            $newInventory->ClassificationId = $inventory->ClassificationId;
            $newInventory->IsDeleted = false;
            $newInventory->DateCreated = Carbon::now('Asia/Manila');
            $newInventory->CreatedById = Auth::id();
            $newInventory->ModifiedById = Auth::id();
            $newInventory->DateModified = Carbon::now('Asia/Manila');

            if ($type === 'in') {
                $newInventory->StocksAdded = $quantity;
                $newInventory->StockOut = 0;
                $newInventory->StocksAvailable = $item->StocksAvailable + $quantity;
                $item->StocksAvailable += $quantity;
                $message = "Successfully added {$quantity} items";
            } else {
                $newInventory->StocksAdded = 0;
                $newInventory->StockOut = $quantity;
                $newInventory->StocksAvailable = $item->StocksAvailable - $quantity;
                $item->StocksAvailable -= $quantity;
                $message = "Successfully removed {$quantity} items";
            }

            $newInventory->save();
            $item->save();

            DB::commit();

            return redirect()->route('inventory.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('inventory.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $userPermissions = $this->getUserPermissions();
            
            if (!$userPermissions || !$userPermissions->CanDelete) {
                return redirect()->back()->with('error', 'You do not have permission to delete inventory records.');
            }

            $inventory = Inventory::findOrFail($id);
            $inventory->IsDeleted = true;
            $inventory->DeletedById = auth()->id();
            $inventory->DateDeleted = now();
            $inventory->save();

            return redirect()->route('inventory.index')
                ->with('success', 'Inventory record deleted successfully');

        } catch (\Exception $e) {
            \Log::error('Error deleting inventory: ' . $e->getMessage());
            return back()->with('error', 'Error deleting inventory: ' . $e->getMessage());
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
            $inventory->RestoredById = Auth::user()->UserAccountID;

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

    private function getUserPermissions()
    {
        $userRole = auth()->user()->role;
        return RolePolicy::whereHas('role', function($query) use ($userRole) {
            $query->where('RoleName', $userRole);
        })->where('Module', 'Inventory')->first();
    }
}