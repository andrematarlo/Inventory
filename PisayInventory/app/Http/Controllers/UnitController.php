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
            // Check permissions first
            $userPermissions = $this->getUserPermissions();
            if (!$userPermissions || !$userPermissions->CanDelete) {
                return redirect()->back()->with('error', 'You do not have permission to delete units.');
            }

            DB::beginTransaction();
            
            // Find the unit using the correct table name - unitofmeasure
            $unit = DB::table('unitofmeasure')->where('UnitOfMeasureId', $id)->first();
            
            if (!$unit) {
                throw new \Exception('Unit not found');
            }
            
            // Check if the unit is associated with any items
            // Update the table name for items if needed
            $itemsCount = DB::table('menu_items')
                ->where('UnitOfMeasureID', $id)
                ->count();
            
            if ($itemsCount > 0) {
                // Soft delete if used in items
                DB::table('unitofmeasure')
                    ->where('UnitOfMeasureId', $id)
                    ->update([
                        'IsDeleted' => true,
                        'DeletedById' => Auth::id(),
                        'DateDeleted' => now()
                    ]);
            } else {
                // Hard delete if no items are associated
                DB::table('unitofmeasure')
                    ->where('UnitOfMeasureId', $id)
                    ->delete();
            }
            
            DB::commit();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Unit deleted successfully'
                ]);
            }
            
            return redirect()->route('units.index')
                ->with('success', 'Unit deleted successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting unit: ' . $e->getMessage());
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete unit: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('units.index')
                ->with('error', 'Failed to delete unit: ' . $e->getMessage());
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
