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
    public function index(Request $request)
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
            
        $showDeleted = $request->has('deleted') || $request->segment(3) === 'trash';

        return view('units.index', compact('units', 'trashedUnits', 'userPermissions', 'showDeleted'));
    }

    /**
     * Display a listing of trashed resources.
     */
    public function trash()
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
            
        $showDeleted = true;

        return view('units.index', compact('units', 'trashedUnits', 'userPermissions', 'showDeleted'));
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

            try {
                // Find the max UnitOfMeasureId directly with DB query to be more reliable
                $maxId = DB::table('unitofmeasure')->max('UnitOfMeasureId');
            } catch (\Exception $dbEx) {
                // Try with exact case matching if the first attempt fails
                Log::warning('First DB query failed, trying with exact case: ' . $dbEx->getMessage());
                $maxId = DB::table('UnitOfMeasure')->max('UnitOfMeasureId');
            }
            
            $nextId = $maxId ? $maxId + 1 : 1;

            // Log the generated ID for debugging
            Log::info('Creating new unit with ID: ' . $nextId, [
                'max_found_id' => $maxId,
                'unit_name' => $request->UnitName
            ]);

            try {
                // Use direct DB insert instead of Eloquent create
                DB::table('unitofmeasure')->insert([
                    'UnitOfMeasureId' => $nextId,
                    'UnitName' => $request->UnitName,
                    'CreatedById' => Auth::id(),
                    'DateCreated' => now(),
                    'IsDeleted' => false
                ]);
            } catch (\Exception $insertEx) {
                // Try with exact case matching if the first attempt fails
                Log::warning('Insert failed, trying with exact case: ' . $insertEx->getMessage());
                DB::table('UnitOfMeasure')->insert([
                    'UnitOfMeasureId' => $nextId,
                    'UnitName' => $request->UnitName,
                    'CreatedById' => Auth::id(),
                    'DateCreated' => now(),
                    'IsDeleted' => false
                ]);
            }

            DB::commit();
            return redirect()->route('units.index')
                ->with('success', 'Unit added successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating unit:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
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
            
            // Find the unit using Eloquent model
            $unit = UnitOfMeasure::where('UnitOfMeasureId', $id)->first();
            
            if (!$unit) {
                throw new \Exception('Unit not found');
            }
            
            // Check if the unit is associated with any items
            $itemsCount = DB::table('items')
                ->where('UnitOfMeasureId', $id)
                ->where('IsDeleted', false)
                ->count();
            
            // Always use soft delete for consistency
            $unit->update([
                'IsDeleted' => true,
                'DeletedById' => Auth::id(),
                'DateDeleted' => now()
            ]);
            
            DB::commit();
            
            // Redirect to the units page with the deleted tab active
            $redirectUrl = route('units.index') . '#deleted';
            
            if ($itemsCount > 0) {
                return redirect($redirectUrl)
                    ->with('success', 'Unit was moved to trash. It is being used by ' . $itemsCount . ' items.');
            } else {
                return redirect($redirectUrl)
                    ->with('success', 'Unit moved to trash successfully.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting unit: ' . $e->getMessage());
            
            return redirect(route('units.index') . '#deleted')
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
            
            // Check for redirect hash
            $redirectHash = request('redirect_hash', '');
            
            $redirectUrl = route('units.index');
            if (!empty($redirectHash)) {
                $redirectUrl .= $redirectHash;
            }
            
            return redirect($redirectUrl)
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
