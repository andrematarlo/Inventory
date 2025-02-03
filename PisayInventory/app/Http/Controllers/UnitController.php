<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::where('IsDeleted', false)
            ->with('createdBy')
            ->get();
        return view('units.index', compact('units'));
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
            'UnitName' => 'required|unique:UnitOfMeasure,UnitName'
        ]);

        $unit = new Unit();
        $unit->UnitName = $request->UnitName;
        $unit->CreatedById = Auth::id();
        $unit->DateCreated = now();
        $unit->save();

        return redirect()->route('units.index')->with('success', 'Unit added successfully');
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
    public function update(Request $request, $unit)
    {
        $unitOfMeasure = Unit::findOrFail($unit);
        
        $request->validate([
            'UnitName' => 'required|unique:UnitOfMeasure,UnitName,' . $unitOfMeasure->UnitOfMeasureId . ',UnitOfMeasureId'
        ]);

        $unitOfMeasure->UnitName = $request->UnitName;
        $unitOfMeasure->save();

        return redirect()->route('units.index')->with('success', 'Unit updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($unit)
    {
        $unitOfMeasure = Unit::findOrFail($unit);
        $unitOfMeasure->delete();

        return redirect()->route('units.index')->with('success', 'Unit deleted successfully');
    }
}
