<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::with(['created_by_user', 'modified_by_user'])
            ->where('IsDeleted', 0)
            ->orderBy('SupplierName')
            ->get();

        $trashedSuppliers = Supplier::with(['created_by_user', 'modified_by_user', 'deleted_by_user'])
            ->where('IsDeleted', 1)
            ->orderBy('SupplierName')
            ->get();

        return view('suppliers.index', compact('suppliers', 'trashedSuppliers'));
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
                'SupplierName' => 'required|string|max:255',
                'ContactNum' => 'nullable|string|max:20',
                'Address' => 'nullable|string',
            ]);

            // Get the last SupplierID
            $lastSupplier = Supplier::orderBy('SupplierID', 'desc')->first();
            $nextSupplierId = $lastSupplier ? $lastSupplier->SupplierID + 1 : 1;

            $now = Carbon::now('Asia/Manila');

            Supplier::create([
                'SupplierID' => $nextSupplierId,
                'SupplierName' => $request->SupplierName,
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'SupplierName' => 'required|string|max:255',
            'ContactNum' => 'nullable|string|max:20',
            'Address' => 'nullable|string',
        ]);

        $supplier = Supplier::findOrFail($id);
        
        $supplier->update([
            'SupplierName' => $request->SupplierName,
            'ContactNum' => $request->ContactNum,
            'Address' => $request->Address,
            'ModifiedById' => auth()->user()->UserAccountID,
            'DateModified' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully');
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
        $supplier = Supplier::findOrFail($id);
        
        $supplier->update([
            'IsDeleted' => false,
            'DeletedById' => null,
            'DateDeleted' => null,
            'ModifiedById' => auth()->user()->UserAccountID,
            'DateModified' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier restored successfully');
    }
}
