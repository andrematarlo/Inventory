<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use Illuminate\Support\Facades\Storage;
use App\Models\RolePolicy;
use App\Models\Item;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $activeSuppliers = Supplier::where('IsDeleted', false)
                ->with(['items' => function($query) {
                    $query->where('items.IsDeleted', false);
                }])
                ->orderBy('CompanyName')
                ->paginate(10);

            $deletedSuppliers = Supplier::where('IsDeleted', true)
                ->orderBy('CompanyName')
                ->paginate(10);

            $items = Item::where('IsDeleted', false)
                ->orderBy('ItemName')
                ->get();

            $userPermissions = $this->getUserPermissions();

            return view('suppliers.index', [
                'activeSuppliers' => $activeSuppliers,
                'deletedSuppliers' => $deletedSuppliers,
                'userPermissions' => $userPermissions,
                'items' => $items
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading suppliers: ' . $e->getMessage());
            return back()->with('error', 'Error loading suppliers: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $items = Item::where('IsDeleted', false)->get();
        return view('suppliers.create', compact('items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            Log::info('Starting supplier creation with data:', [
                'request_data' => $request->all(),
                'items' => $request->input('items')
            ]);

            $validated = $request->validate([
                'CompanyName' => 'required|string|max:255',
                'ContactPerson' => 'required|string|max:255',
                'TelephoneNumber' => 'nullable|string|max:20',
                'ContactNum' => 'required|string|max:20',
                'Address' => 'required|string',
                'items' => 'nullable|array',
                'items.*' => 'exists:items,ItemId'
            ]);

            Log::info('Validation passed:', [
                'validated_data' => $validated,
                'items' => $validated['items'] ?? []
            ]);

            // Get the next SupplierID
            $lastSupplier = Supplier::orderBy('SupplierID', 'desc')->first();
            $nextSupplierId = $lastSupplier ? $lastSupplier->SupplierID + 1 : 1;

            $supplier = new Supplier();
            $supplier->SupplierID = $nextSupplierId;
            $supplier->CompanyName = $validated['CompanyName'];
            $supplier->ContactPerson = $validated['ContactPerson'];
            $supplier->TelephoneNumber = $validated['TelephoneNumber'];
            $supplier->ContactNum = $validated['ContactNum'];
            $supplier->Address = $validated['Address'];
            $supplier->CreatedById = Auth::id();
            $supplier->DateCreated = now();
            $supplier->IsDeleted = false;
            
            Log::info('About to save supplier:', [
                'supplier_data' => $supplier->toArray()
            ]);
            
            $supplier->save();

            // Attach items to supplier with the correct pivot data
            if (!empty($validated['items'])) {
                $now = now();
                $itemData = [];
                
                Log::info('Processing items to attach:', [
                    'items' => $validated['items']
                ]);

                foreach ($validated['items'] as $itemId) {
                    // Insert directly into the pivot table
                    DB::table('items_suppliers')->insert([
                        'ItemId' => $itemId,
                        'SupplierID' => $supplier->SupplierID,
                        'DateCreated' => $now,
                        'CreatedById' => Auth::id(),
                        'DateModified' => $now,
                        'ModifiedById' => Auth::id(),
                        'IsDeleted' => false
                    ]);
                }
                
                Log::info('Items attached successfully');

                // Verify items were attached
                $attachedItems = DB::table('items_suppliers')
                    ->where('SupplierID', $supplier->SupplierID)
                    ->where('IsDeleted', false)
                    ->pluck('ItemId')
                    ->toArray();

                Log::info('Verified attached items:', [
                    'attached_items' => $attachedItems,
                    'count' => count($attachedItems)
                ]);
            } else {
                Log::info('No items to attach');
            }

            DB::commit();
            Log::info('Supplier created successfully');
            return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating supplier:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error creating supplier: ' . $e->getMessage())->withInput();
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
    public function edit($id)
    {
        try {
            $supplier = Supplier::where('SupplierID', $id)->firstOrFail();
            $items = Item::where('IsDeleted', false)->get();
            return view('suppliers.edit', compact('supplier', 'items'));
        } catch (\Exception $e) {
            Log::error('Error loading supplier for edit:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('suppliers.index')
                ->with('error', 'Supplier not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info('Starting supplier update:', [
                'id' => $id,
                'request_data' => $request->all()
            ]);

            // Validate the request
            $validated = $request->validate([
                'CompanyName' => 'required|string|max:255',
                'ContactPerson' => 'required|string|max:255',
                'ContactNum' => 'required|string|max:20',
                'TelephoneNumber' => 'nullable|string|max:20',
                'Address' => 'required|string|max:500',
                'items' => 'nullable|array',
                'items.*' => 'exists:items,ItemId'
            ]);

            Log::info('Validation passed:', [
                'validated_data' => $validated
            ]);

            DB::beginTransaction();

            // Find the supplier
            Log::info('Looking for supplier with ID:', ['id' => $id]);
            
            // Check if ID is an object or array
            if (is_object($id) || is_array($id)) {
                Log::info('ID is an object/array:', ['id' => $id]);
                if (isset($id->SupplierID)) {
                    $id = $id->SupplierID;
                } elseif (is_array($id) && isset($id['SupplierID'])) {
                    $id = $id['SupplierID'];
                }
            }

            // If it's a string containing JSON, try to decode it
            if (is_string($id) && strpos($id, '{') !== false) {
                Log::info('ID appears to be JSON string:', ['id' => $id]);
                $decoded = json_decode($id, true);
                if (isset($decoded['SupplierID'])) {
                    $id = $decoded['SupplierID'];
                }
            }

            $supplier = Supplier::where('SupplierID', $id)->first();
            
            if (!$supplier) {
                Log::error('Supplier not found:', [
                    'id' => $id,
                    'id_type' => gettype($id),
                    'id_value' => $id
                ]);
                throw new \Exception('Supplier not found');
            }

            Log::info('Found supplier:', [
                'supplier_id' => $supplier->SupplierID,
                'company_name' => $supplier->CompanyName
            ]);

            // Update supplier basic information
            $supplier->CompanyName = $validated['CompanyName'];
            $supplier->ContactPerson = $validated['ContactPerson'];
            $supplier->ContactNum = $validated['ContactNum'];
            $supplier->TelephoneNumber = $validated['TelephoneNumber'];
            $supplier->Address = $validated['Address'];
            $supplier->ModifiedById = Auth::id();
            $supplier->DateModified = now();
            $supplier->save();

            // Handle items relationships
            if (isset($validated['items'])) {
                // Get current items
                $currentItems = DB::table('items_suppliers')
                    ->where('SupplierID', $supplier->SupplierID)
                    ->where('IsDeleted', false)
                    ->pluck('ItemId')
                    ->toArray();

                $newItems = $validated['items'];

                // Items to remove
                $itemsToRemove = array_diff($currentItems, $newItems);
                
                // Items to add
                $itemsToAdd = array_diff($newItems, $currentItems);

                // Mark items as deleted
                if (!empty($itemsToRemove)) {
                    DB::table('items_suppliers')
                        ->where('SupplierID', $supplier->SupplierID)
                        ->whereIn('ItemId', $itemsToRemove)
                        ->update([
                            'IsDeleted' => true,
                            'DateModified' => now(),
                            'ModifiedById' => Auth::id()
                        ]);
                }

                // Add new items
                foreach ($itemsToAdd as $itemId) {
                    DB::table('items_suppliers')->insert([
                        'SupplierID' => $supplier->SupplierID,
                        'ItemId' => $itemId,
                        'DateCreated' => now(),
                        'CreatedById' => Auth::id(),
                        'DateModified' => now(),
                        'ModifiedById' => Auth::id(),
                        'IsDeleted' => false
                    ]);
                }

                // Update existing items
                if (!empty($newItems)) {
                    DB::table('items_suppliers')
                        ->where('SupplierID', $supplier->SupplierID)
                        ->whereIn('ItemId', $newItems)
                        ->update([
                            'IsDeleted' => false,
                            'DateModified' => now(),
                            'ModifiedById' => Auth::id()
                        ]);
                }
            } else {
                // If no items selected, mark all as deleted
                DB::table('items_suppliers')
                    ->where('SupplierID', $supplier->SupplierID)
                    ->update([
                        'IsDeleted' => true,
                        'DateModified' => now(),
                        'ModifiedById' => Auth::id()
                    ]);
            }

            DB::commit();

            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating supplier:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->withInput()
                ->with('error', 'Error updating supplier. Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Debug logging
            Log::info('Attempting to delete supplier:', ['id' => $id]);

            // Extract ID from object if needed
            if (is_object($id) || is_array($id)) {
                if (isset($id->SupplierID)) {
                    $id = $id->SupplierID;
                } elseif (is_array($id) && isset($id['SupplierID'])) {
                    $id = $id['SupplierID'];
                }
            }

            // If it's a string containing JSON, try to decode it
            if (is_string($id) && strpos($id, '{') !== false) {
                $decoded = json_decode($id, true);
                if (isset($decoded['SupplierID'])) {
                    $id = $decoded['SupplierID'];
                }
            }

            // Final validation
            if (!is_numeric($id)) {
                throw new \Exception('Invalid supplier ID');
            }

            // Find the supplier
            $supplier = Supplier::where('SupplierID', $id)->first();
            if (!$supplier) {
                throw new \Exception('Supplier not found');
            }

            // Check permissions
            $userPermissions = $this->getUserPermissions();
            if (!$userPermissions || !$userPermissions->CanDelete) {
                throw new \Exception('You do not have permission to delete suppliers.');
            }

            // Perform soft delete
            $supplier->update([
                'IsDeleted' => true,
                'DeletedById' => Auth::id(),
                'DateDeleted' => now()
            ]);

            DB::commit();
            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier moved to trash successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting supplier:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error deleting supplier: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            DB::beginTransaction();

            $supplier = Supplier::where('SupplierID', $id)
                ->where('IsDeleted', true)
                ->firstOrFail();

            $supplier->update([
                'IsDeleted' => false,
                'RestoredByID' => Auth::id(),
                'DateRestored' => now(),
                'DeletedByID' => null,
                'DateDeleted' => null
            ]);

            DB::commit();
            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier restored successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Supplier restore failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to restore supplier: ' . $e->getMessage());
        }
    }

    public function getUserPermissions($module = null)
    {
        return parent::getUserPermissions('Suppliers');
    }
}
