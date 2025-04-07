<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use App\Models\RolePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClassificationController extends Controller
{
    public function getUserPermissions($module = null)
    {
        try {
            if (!Auth::check()) {
                return (object)[
                    'CanAdd' => false,
                    'CanEdit' => false,
                    'CanDelete' => false,
                    'CanView' => false
                ];
            }

            return parent::getUserPermissions('Classifications');

        } catch (\Exception $e) {
            Log::error('Error getting permissions: ' . $e->getMessage());
            return (object)[
                'CanAdd' => false,
                'CanEdit' => false,
                'CanDelete' => false,
                'CanView' => false
            ];
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userPermissions = $this->getUserPermissions();

        // Check if user has View permission
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->back()->with('error', 'You do not have permission to view classifications.');
        }

        $classifications = Classification::with(['created_by_user', 'modified_by_user'])
            ->where('IsDeleted', 0)
            ->orderBy('ClassificationName')
            ->paginate(10);

        $trashedClassifications = Classification::with(['created_by_user', 'modified_by_user', 'deleted_by_user'])
            ->where('IsDeleted', 1)
            ->orderBy('ClassificationName')
            ->paginate(10);

        // Make sure $trashedClassifications is being created properly
        \Illuminate\Support\Facades\Log::info('Trashed classifications count: ' . $trashedClassifications->count());

        return view('classifications.index', compact('classifications', 'trashedClassifications', 'userPermissions'));
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
        $userPermissions = $this->getUserPermissions();
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return redirect()->back()->with('error', 'You do not have permission to add classifications.');
        }

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
        $userPermissions = $this->getUserPermissions();
        if (!$userPermissions || !$userPermissions->CanEdit) {
            return redirect()->back()->with('error', 'You do not have permission to edit classifications.');
        }

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
        $userPermissions = $this->getUserPermissions();
        if (!$userPermissions || !$userPermissions->CanEdit) {
            return redirect()->back()->with('error', 'You do not have permission to edit classifications.');
        }

        try {
            DB::beginTransaction();

            // Clean and extract the ID
            if (is_object($id)) {
                $id = $id->ClassificationId;
            } else if (is_string($id) && strpos($id, '{') !== false) {
                $data = json_decode($id, true);
                $id = $data['ClassificationId'] ?? null;
            }

            // Verify we have a valid ID
            if (!$id) {
                throw new \Exception('Invalid Classification ID');
            }

            $request->validate([
                'ClassificationName' => 'required|string|max:255',
                'ParentClassificationId' => 'nullable|exists:classification,ClassificationId'
            ]);

            $classification = Classification::where('ClassificationId', $id)->first();
            
            if (!$classification) {
                throw new \Exception('Classification not found');
            }

            $classification->update([
                'ClassificationName' => $request->ClassificationName,
                'ParentClassificationId' => $request->ParentClassificationId,
                'ModifiedById' => auth()->user()->UserAccountID,
                'DateModified' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            DB::commit();
            return back()->with('success', 'Classification updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Classification update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update classification: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $userPermissions = $this->getUserPermissions();
        if (!$userPermissions || !$userPermissions->CanDelete) {
            return redirect()->back()->with('error', 'You do not have permission to delete classifications.');
        }

        try {
            DB::beginTransaction();
            
            // Find the classification first
            $classification = Classification::findOrFail($id);
            
            // Now perform the soft delete
            $classification->update([
                'IsDeleted' => true,
                'DeletedById' => Auth::id(),
                'DateDeleted' => now()
            ]);
            
            DB::commit();
            return redirect()->route('classifications.index')
                ->with('success', 'Classification successfully moved to trash');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete classification: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            DB::beginTransaction();
            
            $userPermissions = $this->getUserPermissions();
            if (!$userPermissions || !$userPermissions->CanEdit) {
                return redirect()->back()->with('error', 'You do not have permission to restore classifications.');
            }

            $classification = Classification::findOrFail($id);
            $classification->update([
                'IsDeleted' => false,
                'DeletedById' => null,
                'DateDeleted' => null,
                'RestoredById' => Auth::id(),
                'DateRestored' => now(),
                'ModifiedById' => null,
                'DateModified' => null
            ]);

            DB::commit();
            return redirect()->route('classifications.index')
                ->with('success', 'Classification restored successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Classification restore failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to restore classification: ' . $e->getMessage());
        }
    }
}
