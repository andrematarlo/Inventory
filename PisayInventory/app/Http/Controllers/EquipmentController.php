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

            // Get all equipment with their relationships
            $equipment = Equipment::with(['laboratory'])
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
        $userPermissions = $this->getUserPermissions();
        if (!$userPermissions->CanAdd) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'equipment_id' => 'required|string|max:50|unique:equipment,equipment_id',
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

            $validated['created_by'] = Auth::id();
            $equipment = Equipment::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Equipment created successfully',
                'data' => $equipment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create equipment: ' . $e->getMessage()
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
            $equipment = Equipment::findOrFail($id);
            
            if ($equipment->borrowings()->where('actual_return_date', null)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete equipment that is currently borrowed.'
                ], 422);
            }

            $equipment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Equipment deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting equipment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting equipment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $equipment = Equipment::withTrashed()->findOrFail($id);
            $equipment->restore();

            return response()->json([
                'success' => true,
                'message' => 'Equipment restored successfully.'
            ]);
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