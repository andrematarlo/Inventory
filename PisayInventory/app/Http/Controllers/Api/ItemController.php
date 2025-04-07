<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::all();
        return response()->json($items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'classification_id' => 'nullable|exists:classifications,id',
            'unit_id' => 'nullable|exists:units,id',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        $item = Item::create($validatedData);
        return response()->json($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Item::findOrFail($id);
        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = Item::findOrFail($id);
        
        $validatedData = $request->validate([
            'item_name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'classification_id' => 'nullable|exists:classifications,id',
            'unit_id' => 'nullable|exists:units,id',
            'quantity' => 'sometimes|required|integer|min:0',
            'price' => 'sometimes|required|numeric|min:0',
        ]);

        $item->update($validatedData);
        return response()->json($item);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Item::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
} 