<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\UnitOfMeasure;
use App\Models\Classification;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with(['unitOfMeasure', 'classification', 'supplier'])
                    ->where('IsDeleted', false)
                    ->get();
        $units = UnitOfMeasure::all();
        $classifications = Classification::all();
        $suppliers = Supplier::where('IsDeleted', false)->get();

        return view('items.index', compact('items', 'units', 'classifications', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ItemName' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'UnitOfMeasureId' => 'required|exists:UnitOfMeasure,UnitOfMeasureId',
            'ClassificationId' => 'required|exists:Classification,ClassificationId',
            'SupplierID' => 'required|exists:Suppliers,SupplierID',
        ]);

        Item::create([
            'ItemName' => $request->ItemName,
            'Description' => $request->Description,
            'UnitOfMeasureId' => $request->UnitOfMeasureId,
            'ClassificationId' => $request->ClassificationId,
            'SupplierID' => $request->SupplierID,
        ]);

        return redirect()->route('items.index')->with('success', 'Item added successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'ItemName' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'UnitOfMeasureId' => 'required|exists:UnitOfMeasure,UnitOfMeasureId',
            'ClassificationId' => 'required|exists:Classification,ClassificationId',
            'SupplierID' => 'required|exists:Suppliers,SupplierID',
        ]);

        $item = Item::findOrFail($id);
        $item->update([
            'ItemName' => $request->ItemName,
            'Description' => $request->Description,
            'UnitOfMeasureId' => $request->UnitOfMeasureId,
            'ClassificationId' => $request->ClassificationId,
            'SupplierID' => $request->SupplierID,
        ]);

        return redirect()->route('items.index')->with('success', 'Item updated successfully');
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->update([
            'IsDeleted' => true,
            'DeletedById' => auth()->id(),
            'DateDeleted' => Carbon::now()
        ]);

        return redirect()->route('items.index')->with('success', 'Item deleted successfully');
    }
} 