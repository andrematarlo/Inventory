<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        return response()->json($classifications);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $classification = Classification::create($validatedData);
        return response()->json($classification, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $classification = Classification::findOrFail($id);
        return response()->json($classification);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $classification = Classification::findOrFail($id);
        
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $classification->update($validatedData);
        return response()->json($classification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $classification = Classification::findOrFail($id);
        $classification->delete();
        return response()->json(null, 204);
    }
} 