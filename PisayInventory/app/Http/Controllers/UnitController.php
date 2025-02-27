<?php

namespace App\Http\Controllers;

use App\Models\UnitOfMeasure;
use App\Models\RolePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Item;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userPermissions = $this->getUserPermissions();
        
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->back()->with('error', 'You do not have permission to view units.');
        }

        $units = UnitOfMeasure::where('IsDeleted', false)
            ->orderBy('UnitName')
            ->get();

        $trashedUnits = UnitOfMeasure::where('IsDeleted', true)
            ->orderBy('UnitName')
            ->get();

        return view('units.index', compact('units', 'trashedUnits', 'userPermissions'));
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

            // Validate request
            $request->validate([
                'UnitName' => 'required|unique:UnitOfMeasure,UnitName'
            ]);

            // Find the last UnitOfMeasureId
            $lastUnit = UnitOfMeasure::orderBy('UnitOfMeasureId', 'desc')->first();
            $nextId = $lastUnit ? $lastUnit->UnitOfMeasureId + 1 : 1;

            // Create new unit
            UnitOfMeasure::create([
                'UnitOfMeasureId' => $nextId,
                'UnitName' => $request->UnitName,
                'CreatedById' => Auth::id(),
                'DateCreated' => now(),
                'IsDeleted' => false
            ]);

            DB::commit();
            return redirect()->route('units.index')
                ->with('success', 'Unit added successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating unit:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error creating unit: ' . $e->getMessage());
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
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Find the unit
            $unit = UnitOfMeasure::where('UnitOfMeasureId', $id)->first();
            if (!$unit) {
                throw new \Exception('Unit not found');
            }

            // Check permissions
            $userPermissions = $this->getUserPermissions();
            if (!$userPermissions || !$userPermissions->CanEdit) {
                throw new \Exception('You do not have permission to edit units.');
            }
            
            // Validate request
            $request->validate([
                'UnitName' => 'required|unique:UnitOfMeasure,UnitName,' . $unit->UnitOfMeasureId . ',UnitOfMeasureId'
            ]);

            // Update the unit
            $unit->update([
                'UnitName' => $request->UnitName,
                'ModifiedById' => Auth::id(),
                'DateModified' => now()
            ]);

            DB::commit();
            return redirect()->route('units.index')
                ->with('success', 'Unit updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating unit:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error updating unit: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Find the unit
            $unit = UnitOfMeasure::where('UnitOfMeasureId', $id)->first();
            if (!$unit) {
                throw new \Exception('Unit not found');
            }

            // Check permissions
            $userPermissions = $this->getUserPermissions();
            if (!$userPermissions || !$userPermissions->CanDelete) {
                throw new \Exception('You do not have permission to delete units.');
            }

            // Check if unit is being used by any items
            $itemCount = Item::where('UnitOfMeasureId', $id)
                ->where('IsDeleted', false)
                ->count();
            
            if ($itemCount > 0) {
                throw new \Exception("Cannot delete unit because it is being used by {$itemCount} items.");
            }

            // Perform soft delete
            $unit->update([
                'IsDeleted' => true,
                'DeletedById' => Auth::id(),
                'DateDeleted' => now()
            ]);

            DB::commit();
            return redirect()->route('units.index')
                ->with('success', 'Unit moved to trash successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting unit:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error deleting unit: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            DB::beginTransaction();

            // Find the unit
            $unit = UnitOfMeasure::where('UnitOfMeasureId', $id)
                ->where('IsDeleted', true)
                ->first();

            if (!$unit) {
                throw new \Exception('Unit not found or already restored');
            }

            // Check permissions
            $userPermissions = $this->getUserPermissions();
            if (!$userPermissions || !$userPermissions->CanEdit) {
                throw new \Exception('You do not have permission to restore units.');
            }

            // Restore the unit
            $unit->update([
                'IsDeleted' => false,
                'DeletedById' => null,
                'DateDeleted' => null,
                'RestoredById' => Auth::id(),
                'DateRestored' => now(),
                'ModifiedById' => null,
                'DateModified' => null
            ]);

            DB::commit();
            return redirect()->route('units.index')
                ->with('success', 'Unit restored successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restoring unit:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error restoring unit: ' . $e->getMessage());
        }
    }

    public function getUserPermissions($module = null)
    {
        return parent::getUserPermissions('Units');
    }
}
