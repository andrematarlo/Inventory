<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReagentItemController extends Controller
{
    public function index()
    {
        $items = DB::table('laboratory_reagent_items')->orderBy('reagent_item_id', 'desc')->get();
        return view('reagent_items.index', compact('items'));
    }

    public function create()
    {
        return view('reagent_items.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer',
            'reagent' => 'required|string',
        ]);
        DB::table('laboratory_reagent_items')->insert([
            'quantity' => $request->quantity,
            'reagent' => $request->reagent,
            'sds_checked' => $request->sds_checked ? 1 : 0,
            'issued_amount' => $request->issued_amount,
            'remarks' => $request->remarks,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('reagent-items.index')->with('success', 'Reagent item added.');
    }

    public function edit($id)
    {
        $item = DB::table('laboratory_reagent_items')->where('reagent_item_id', $id)->first();
        return view('reagent_items.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer',
            'reagent' => 'required|string',
        ]);
        DB::table('laboratory_reagent_items')->where('reagent_item_id', $id)->update([
            'quantity' => $request->quantity,
            'reagent' => $request->reagent,
            'sds_checked' => $request->sds_checked ? 1 : 0,
            'issued_amount' => $request->issued_amount,
            'remarks' => $request->remarks,
            'updated_at' => now(),
        ]);
        return redirect()->route('reagent-items.index')->with('success', 'Reagent item updated.');
    }

    public function destroy($id)
    {
        DB::table('laboratory_reagent_items')->where('reagent_item_id', $id)->delete();
        return redirect()->route('reagent-items.index')->with('success', 'Reagent item deleted.');
    }
} 