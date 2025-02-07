<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Unit;

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

        try {
            // Find the last UnitOfMeasureId
            $lastUnit = Unit::orderBy('UnitOfMeasureId', 'desc')->first();
            $nextId = $lastUnit ? $lastUnit->UnitOfMeasureId + 1 : 1;

            $unit = new Unit();
            $unit->UnitOfMeasureId = $nextId;  // Manually set the ID
            $unit->UnitName = $request->UnitName;
            $unit->CreatedById = Auth::id();
            $unit->DateCreated = now();
            $unit->IsDeleted = 0;  // Explicitly set IsDeleted
            $unit->save();

            return redirect()->route('units.index')->with('success', 'Unit added successfully');
        } catch (\Exception $e) {
            \Log::error('Unit Creation Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to create unit: ' . $e->getMessage());
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
