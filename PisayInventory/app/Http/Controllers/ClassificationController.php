<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClassificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classifications = Classification::where('IsDeleted', 0)->get();
        $trashedClassifications = Classification::where('IsDeleted', 1)->get();
        return view('classifications.index', compact('classifications', 'trashedClassifications'));
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
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'ClassificationName' => 'required|string|max:255|unique:classification,ClassificationName'
            ]);

            Classification::create([
                'ClassificationName' => $validated['ClassificationName'],
                'CreatedById' => Auth::user()->UserAccountID,
                'DateCreated' => Carbon::now()->format('Y-m-d H:i:s'),
                'IsDeleted' => false
            ]);

            DB::commit();
            return back()->with('success', 'Classification created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create classification: ' . $e->getMessage())
                        ->withInput();
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
        $classification = Classification::findOrFail($id);
        $classifications = Classification::where('IsDeleted', 0)
            ->where('ClassificationId', '!=', $id)
            ->get();
        return view('classifications.edit', compact('classification', 'classifications'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'ClassificationName' => 'required|string|max:255',
            'ParentClassificationId' => 'nullable|exists:classification,ClassificationId'
        ]);

        $classification = Classification::findOrFail($id);
        $classification->update([
            'ClassificationName' => $request->ClassificationName,
            'ParentClassificationId' => $request->ParentClassificationId,
            'ModifiedById' => auth()->user()->UserAccountID,
            'DateModified' => Carbon::now()->format('Y-m-d H:i:s')
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
        
        $classification->update([
            'IsDeleted' => true,
            'DeletedById' => Auth::user()->UserAccountID,
            'DateDeleted' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        return redirect()->route('classifications.index')
            ->with('success', 'Classification moved to trash successfully');
    }

    public function restore($id)
    {
        $classification = Classification::findOrFail($id);
        
        $classification->update([
            'IsDeleted' => false,
            'DeletedById' => null,
            'DateDeleted' => null,
            'ModifiedById' => Auth::user()->UserAccountID,
            'DateModified' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        return redirect()->route('classifications.index')
            ->with('success', 'Classification restored successfully');
    }

    public function trash()
    {
        $trashedClassifications = Classification::where('IsDeleted', 1)->get();
        return view('classifications.trash', compact('trashedClassifications'));
    }
}
