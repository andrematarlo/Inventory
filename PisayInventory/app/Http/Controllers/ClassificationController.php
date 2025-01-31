<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use Illuminate\Http\Request;

class ClassificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classifications = Classification::all();
        $parentClassifications = Classification::whereNull('ParentClassificationId')->get();
        return view('classifications.index', compact('classifications', 'parentClassifications'));
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
            'ClassificationName' => 'required|string|max:255',
            'ParentClassificationId' => 'nullable|exists:Classification,ClassificationId'
        ]);

        Classification::create([
            'ClassificationName' => $request->ClassificationName,
            'ParentClassificationId' => $request->ParentClassificationId
        ]);

        return redirect()->route('classifications.index')
            ->with('success', 'Classification added successfully');
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
            'ClassificationName' => 'required|string|max:255',
            'ParentClassificationId' => 'nullable|exists:Classification,ClassificationId'
        ]);

        $classification = Classification::findOrFail($id);
        $classification->update([
            'ClassificationName' => $request->ClassificationName,
            'ParentClassificationId' => $request->ParentClassificationId
        ]);

        return redirect()->route('classifications.index')
            ->with('success', 'Classification updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $classification = Classification::findOrFail($id);
        $classification->delete();

        return redirect()->route('classifications.index')
            ->with('success', 'Classification deleted successfully');
    }
}
