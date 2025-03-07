<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class EquipmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the equipment.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $userPermissions = $this->getUserPermissions('Equipment');
            if (!$userPermissions || !$userPermissions->CanView) {
                return redirect()->route('dashboard')
                    ->with('error', 'You do not have permission to view equipment.');
            }

            // Get all equipment including soft-deleted ones with laboratory relationship
            $equipment = Equipment::with(['laboratory' => function($query) {
                $query->withTrashed(); // Include soft-deleted laboratories if needed
            }])
            ->withTrashed()
            ->orderBy('equipment_name')
            ->get();

            // Get laboratories for the create/edit forms
            $laboratories = Laboratory::orderBy('laboratory_name')->get();

            return view('equipment.index', compact(
                'equipment',
                'laboratories',
                'userPermissions'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading equipment: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Error loading equipment: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating new equipment.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check if user has permission to add equipment
        $userPermissions = $this->getUserPermissions('Equipment');
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return redirect()->route('equipment.index')->with('error', 'You do not have permission to add equipment.');
        }

        $laboratories = Laboratory::orderBy('laboratory_name')->get();
        return view('equipment.create', compact('userPermissions', 'laboratories'));
    }

    /**
     * Store newly created equipment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $userPermissions = $this->getUserPermissions('Equipment');
            if (!$userPermissions || !$userPermissions->CanAdd) {
                return response()->json([
                    'success' => false, 
                    'message' => 'You do not have permission to add equipment.'
                ], 403);
            }

            // Generate unique equipment ID
            $equipmentId = 'EQ' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);

            // Create new equipment
            $equipment = Equipment::create([
                'equipment_id' => $equipmentId,
                'equipment_name' => $request->equipment_name,
                'laboratory_id' => $request->laboratory_id ?: null,
                'description' => $request->description,
                'serial_number' => $request->serial_number,
                'model_number' => $request->model_number,
                'condition' => $request->condition,
                'status' => $request->status,
                'acquisition_date' => $request->acquisition_date ?: null,
                'last_maintenance_date' => $request->last_maintenance_date ?: null,
                'next_maintenance_date' => $request->next_maintenance_date ?: null,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
                'IsDeleted' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Equipment created successfully',
                'data' => $equipment
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating equipment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating equipment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified equipment.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $userPermissions = $this->getUserPermissions();
        if (!$userPermissions->CanView) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $equipment = Equipment::with(['laboratory', 'createdBy', 'updatedBy', 'deletedBy', 'restoredBy'])->findOrFail($id);
        return view('equipment.show', compact('equipment', 'userPermissions'));
    }

    /**
     * Show the form for editing the specified equipment.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Check if user has permission to edit equipment
        $userPermissions = $this->getUserPermissions('Equipment');
        if (!$userPermissions || !$userPermissions->CanEdit) {
            return redirect()->route('equipment.index')->with('error', 'You do not have permission to edit equipment.');
        }

        $equipment = Equipment::findOrFail($id);
        $laboratories = Laboratory::orderBy('laboratory_name')->get();
        
        return view('equipment.edit', compact('equipment', 'laboratories', 'userPermissions'));
    }

    /**
     * Update the specified equipment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $userPermissions = $this->getUserPermissions();
        if (!$userPermissions->CanEdit) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $equipment = Equipment::findOrFail($id);

        $validated = $request->validate([
            'equipment_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'laboratory_id' => 'required|string|exists:laboratories,laboratory_id',
            'serial_number' => 'nullable|string|max:50',
            'model_number' => 'nullable|string|max:50',
            'condition' => 'required|string|max:50',
            'status' => 'required|in:Available,In Use,Under Maintenance',
            'acquisition_date' => 'nullable|date',
            'last_maintenance_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date|after:last_maintenance_date'
        ]);

        try {
            DB::beginTransaction();

            $validated['updated_by'] = Auth::id();
            $equipment->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Equipment updated successfully',
                'data' => $equipment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update equipment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified equipment from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Check permissions
            $userPermissions = $this->getUserPermissions('Equipment');
            if (!$userPermissions || !$userPermissions->CanDelete) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete equipment.'
                ], 403);
            }

            $equipment = Equipment::findOrFail($id);
            
            // Check if equipment is currently borrowed
            if ($equipment->borrowings()->where('actual_return_date', null)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete equipment that is currently borrowed.'
                ], 422);
            }

            DB::beginTransaction();
            try {
                // Update the equipment record
                $equipment->update([
                    'IsDeleted' => true,
                    'deleted_by' => Auth::id(),
                    'deleted_at' => now()
                ]);

                // Perform the soft delete
                $equipment->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Equipment deleted successfully.'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error deleting equipment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting equipment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore the specified equipment.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        try {
            $userPermissions = $this->getUserPermissions('Equipment');
            if (!$userPermissions || !$userPermissions->CanEdit) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to restore equipment.'
                ], 403);
            }

            $equipment = Equipment::withTrashed()->findOrFail($id);
            
            if (!$equipment->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Equipment is not deleted.'
                ]);
            }

            DB::beginTransaction();
            try {
                // Update restoration details
                $equipment->update([
                    'IsDeleted' => false,
                    'RestoredById' => Auth::id(),
                    'DateRestored' => now(),
                    'deleted_at' => null  // Explicitly set deleted_at to null
                ]);
                
                // Restore the soft deleted record
                $equipment->restore();
                
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Equipment restored successfully'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error restoring equipment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error restoring equipment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the permissions for Equipment module.
     *
     * @param string $moduleName
     * @return \App\Models\RolePolicy|null
     */
    public function getUserPermissions($moduleName = 'Equipment')
    {
        return parent::getUserPermissions($moduleName);
    }
} 