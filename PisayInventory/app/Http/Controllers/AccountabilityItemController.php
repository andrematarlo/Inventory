<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountabilityItemController extends Controller
{
    public function index()
    {
        $items = DB::table('laboratory_accountability_items')->orderBy('item_id', 'desc')->get();
        return view('accountability_items.index', compact('items'));
    }

    public function create()
    {
        return view('accountability_items.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer',
            'item' => 'required|string',
        ]);
        DB::table('laboratory_accountability_items')->insert([
            'quantity' => $request->quantity,
            'item' => $request->item,
            'description' => $request->description,
            'issued_condition' => $request->issued_condition,
            'returned_condition' => $request->returned_condition,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('accountability-items.index')->with('success', 'Accountability item added.');
    }

    public function edit($id)
    {
        $item = DB::table('laboratory_accountability_items')->where('item_id', $id)->first();
        return view('accountability_items.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer',
            'item' => 'required|string',
        ]);
        DB::table('laboratory_accountability_items')->where('item_id', $id)->update([
            'quantity' => $request->quantity,
            'item' => $request->item,
            'description' => $request->description,
            'issued_condition' => $request->issued_condition,
            'returned_condition' => $request->returned_condition,
            'updated_at' => now(),
        ]);
        return redirect()->route('accountability-items.index')->with('success', 'Accountability item updated.');
    }

    public function destroy($id)
    {
        DB::table('laboratory_accountability_items')->where('item_id', $id)->delete();
        return redirect()->route('accountability-items.index')->with('success', 'Accountability item deleted.');
    }
} 