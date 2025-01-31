<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\Classification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventory = Inventory::with(['item', 'classification'])
            ->where('IsDeleted', false)
            ->get();
        $items = Item::where('IsDeleted', false)->get();
        $classifications = Classification::all();
        
        return view('inventory.index', compact('inventory', 'items', 'classifications'));
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
            'ItemId' => 'required|exists:Items,ItemId',
            'ClassificationId' => 'required|exists:Classification,ClassificationId',
            'StocksAvailable' => 'required|integer|min:0',
            'StocksAdded' => 'required|integer|min:0'
        ]);

        Inventory::create([
            'ItemId' => $request->ItemId,
            'ClassificationId' => $request->ClassificationId,
            'StocksAvailable' => $request->StocksAvailable,
            'StocksAdded' => $request->StocksAdded,
            'CreatedById' => auth()->id(),
            'DateCreated' => Carbon::now(),
            'IsDeleted' => false
        ]);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory added successfully');
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
    public function update(Request $request, $itemId, $classificationId)
    {
        $request->validate([
            'StocksAvailable' => 'required|integer|min:0',
            'StocksAdded' => 'required|integer|min:0'
        ]);

        $inventory = Inventory::where('ItemId', $itemId)
            ->where('ClassificationId', $classificationId)
            ->firstOrFail();

        $inventory->update([
            'StocksAvailable' => $request->StocksAvailable,
            'StocksAdded' => $request->StocksAdded,
            'ModifiedById' => auth()->id(),
            'DateModified' => Carbon::now()
        ]);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($itemId, $classificationId)
    {
        $inventory = Inventory::where('ItemId', $itemId)
            ->where('ClassificationId', $classificationId)
            ->firstOrFail();

        $inventory->update([
            'IsDeleted' => true,
            'DeletedById' => auth()->id(),
            'DateDeleted' => Carbon::now()
        ]);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory deleted successfully');
    }
}
