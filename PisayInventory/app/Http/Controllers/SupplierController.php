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
        $suppliers = Supplier::where('IsDeleted', false)->get();
        return view('suppliers.index', compact('suppliers'));
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
        $request->validate([
            'SupplierName' => 'required|string|max:255',
            'ContactNum' => 'nullable|string|max:20',
            'Address' => 'nullable|string',
        ]);

        Supplier::create([
            'SupplierName' => $request->SupplierName,
            'ContactNum' => $request->ContactNum,
            'Address' => $request->Address,
            'CreatedById' => Auth::id(),
            'DateCreated' => Carbon::now(),
            'IsDeleted' => false
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier added successfully');
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
            'ModifiedById' => Auth::id(),
            'DateModified' => Carbon::now()
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
            'DeletedById' => Auth::id(),
            'DateDeleted' => Carbon::now()
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully');
    }
}
