<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

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
                ->get();

            $deletedSuppliers = Supplier::where('IsDeleted', true)
                ->orderBy('CompanyName')
                ->get();

            return view('suppliers.index', [
                'activeSuppliers' => $activeSuppliers,
                'deletedSuppliers' => $deletedSuppliers
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'CompanyName' => 'required|string|max:255',
                'ContactPerson' => 'nullable|string|max:255',
                'TelephoneNumber' => 'nullable|string|max:20',
                'ContactNum' => 'nullable|string|max:20',
                'Address' => 'nullable|string',
            ]);

            // Get the last SupplierID
            $lastSupplier = Supplier::orderBy('SupplierID', 'desc')->first();
            $nextSupplierId = $lastSupplier ? $lastSupplier->SupplierID + 1 : 1;

            $now = Carbon::now('Asia/Manila');

            Supplier::create([
                'SupplierID' => $nextSupplierId,
                'CompanyName' => $request->CompanyName,
                'ContactPerson' => $request->ContactPerson,
                'TelephoneNumber' => $request->TelephoneNumber,
                'ContactNum' => $request->ContactNum,
                'Address' => $request->Address,
                'CreatedById' => auth()->user()->UserAccountID,
                'DateCreated' => $now,
                'ModifiedById' => auth()->user()->UserAccountID,
                'DateModified' => $now,
                'IsDeleted' => false
            ]);

            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier added successfully');

        } catch (\Exception $e) {
            \Log::error('Error adding supplier:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('suppliers.index')
                ->with('error', 'Failed to add supplier: ' . $e->getMessage());
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
        try {
            $supplier = Supplier::where('IsDeleted', false)
                ->findOrFail($id);

            return view('suppliers.edit', compact('supplier'));
            
        } catch (\Exception $e) {
            Log::error('Error loading edit supplier form: ' . $e->getMessage());
            return back()->with('error', 'Error loading form: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'CompanyName' => 'required|string|max:255',
                'ContactPerson' => 'required|string|max:255',
                'ContactNum' => 'required|string|max:20',
                'TelephoneNumber' => 'nullable|string|max:20',
                'Address' => 'required|string'
            ]);

            DB::beginTransaction();

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            $supplier = Supplier::findOrFail($id);
            
            $supplier->update([
                'CompanyName' => $request->CompanyName,
                'ContactPerson' => $request->ContactPerson,
                'ContactNum' => $request->ContactNum,
                'TelephoneNumber' => $request->TelephoneNumber,
                'Address' => $request->Address,
                'ModifiedById' => $currentEmployee->EmployeeID,
                'DateModified' => now()
            ]);

            DB::commit();
            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating supplier: ' . $e->getMessage());
            return back()->with('error', 'Error updating supplier: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        
        $supplier->update([
            'IsDeleted' => true,
            'DeletedById' => auth()->user()->UserAccountID,
            'DateDeleted' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier moved to trash successfully');
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
                'RestoredByID' => auth()->id(),
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
}
