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
                ->orderBy('CompanyName')
                ->paginate(10);

            $deletedSuppliers = Supplier::where('IsDeleted', true)
                ->orderBy('CompanyName')
                ->paginate(10);

            $items = Item::where('IsDeleted', false)->get();

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

            $validated = $request->validate([
                'CompanyName' => 'required|string|max:255',
                'ContactPerson' => 'required|string|max:255',
                'TelephoneNumber' => 'nullable|string|max:20',
                'ContactNum' => 'required|string|max:20',
                'Address' => 'required|string',
                'items' => 'nullable|array|exists:items,ItemId'
            ]);

            // Get the next SupplierID
            $lastSupplier = Supplier::orderBy('SupplierID', 'desc')->first();
            $nextSupplierId = $lastSupplier ? $lastSupplier->SupplierID + 1 : 1;

            $supplier = new Supplier();
            $supplier->SupplierID = $nextSupplierId;  // Set the SupplierID manually
            $supplier->CompanyName = $validated['CompanyName'];
            $supplier->ContactPerson = $validated['ContactPerson'];
            $supplier->TelephoneNumber = $validated['TelephoneNumber'];
            $supplier->ContactNum = $validated['ContactNum'];
            $supplier->Address = $validated['Address'];
            $supplier->CreatedById = Auth::id();
            $supplier->DateCreated = now();
            $supplier->IsDeleted = false;
            $supplier->save();

            // Attach items to supplier with the correct pivot data
            if (!empty($validated['items'])) {
                $now = now();
                $itemData = array_fill_keys($validated['items'], [
                    'CreatedById' => Auth::id(),
                    'DateCreated' => $now,
                    'ModifiedById' => Auth::id(),
                    'DateModified' => $now,
                    'IsDeleted' => false
                ]);
                $supplier->items()->attach($itemData);
            }

            DB::commit();
            return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating supplier: ' . $e->getMessage());
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
    public function edit(Supplier $supplier)
    {
        $items = Item::where('IsDeleted', false)->get();
        return view('suppliers.edit', compact('supplier', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'CompanyName' => 'required|string|max:255',
                'ContactPerson' => 'required|string|max:255',
                'TelephoneNumber' => 'nullable|string|max:20',
                'ContactNum' => 'required|string|max:20',
                'Address' => 'required|string',
                'items' => 'nullable|array|exists:items,ItemId'
            ]);

            $supplier->CompanyName = $validated['CompanyName'];
            $supplier->ContactPerson = $validated['ContactPerson'];
            $supplier->TelephoneNumber = $validated['TelephoneNumber'];
            $supplier->ContactNum = $validated['ContactNum'];
            $supplier->Address = $validated['Address'];
            $supplier->ModifiedById = Auth::id();
            $supplier->DateModified = now();
            $supplier->save();

            // Handle items relationship
            if (isset($validated['items'])) {
                // Soft delete removed relationships
                DB::table('items_suppliers')
                    ->where('SupplierID', $supplier->SupplierID)
                    ->whereNotIn('ItemId', $validated['items'])
                    ->update([
                        'IsDeleted' => true,
                        'DeletedById' => Auth::id(),
                        'DateDeleted' => now()
                    ]);

                // Add new relationships and update existing ones
                $now = now();
                foreach ($validated['items'] as $itemId) {
                    DB::table('items_suppliers')
                        ->updateOrInsert(
                            ['SupplierID' => $supplier->SupplierID, 'ItemId' => $itemId],
                            [
                                'CreatedById' => Auth::id(),
                                'DateCreated' => $now,
                                'ModifiedById' => Auth::id(),
                                'DateModified' => $now,
                                'IsDeleted' => false,
                                'DeletedById' => null,
                                'DateDeleted' => null
                            ]
                        );
                }
            } else {
                // Soft delete all relationships if no items selected
                DB::table('items_suppliers')
                    ->where('SupplierID', $supplier->SupplierID)
                    ->update([
                        'IsDeleted' => true,
                        'DeletedById' => Auth::id(),
                        'DateDeleted' => now()
                    ]);
            }

            DB::commit();
            return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating supplier: ' . $e->getMessage())->withInput();
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

    private function getUserPermissions()
    {
        $userRole = Auth::user()->role;
        return RolePolicy::whereHas('role', function($query) use ($userRole) {
            $query->where('RoleName', $userRole);
        })->where('Module', 'Suppliers')->first();
    }
}
