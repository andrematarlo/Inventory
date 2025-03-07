<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LaboratoryController extends Controller
{
    /**
     * Display a listing of the laboratories.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check if user has permission to view laboratories
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view laboratories.');
        }

        $laboratories = Laboratory::withTrashed()
            ->orderBy('laboratory_name')
            ->get();
        
        return view('laboratories.index', compact('laboratories', 'userPermissions'));
    }

    /**
     * Show the form for creating a new laboratory.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check if user has permission to add laboratories
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return redirect()->route('laboratories.index')->with('error', 'You do not have permission to add laboratories.');
        }

        return view('laboratories.create', compact('userPermissions'));
    }

    /**
     * Store a newly created laboratory in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Check if user has permission to add laboratories
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return redirect()->route('laboratories.index')->with('error', 'You do not have permission to add laboratories.');
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'laboratory_id' => 'required|string|max:50|unique:laboratories,laboratory_id',
            'laboratory_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'location' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:Available,Occupied,Under Maintenance'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Create the laboratory
            Laboratory::create([
                'laboratory_id' => $request->laboratory_id,
                'laboratory_name' => $request->laboratory_name,
                'description' => $request->description,
                'location' => $request->location,
                'capacity' => $request->capacity,
                'status' => $request->status,
                'created_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('laboratories.index')->with('success', 'Laboratory created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating laboratory: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified laboratory.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Check if user has permission to view laboratories
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view laboratories.');
        }

        $laboratory = Laboratory::with(['equipment', 'reservations'])->findOrFail($id);
        
        return view('laboratories.show', compact('laboratory', 'userPermissions'));
    }

    /**
     * Show the form for editing the specified laboratory.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Check if user has permission to edit laboratories
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanEdit) {
            return redirect()->route('laboratories.index')->with('error', 'You do not have permission to edit laboratories.');
        }

        $laboratory = Laboratory::findOrFail($id);
        
        return view('laboratories.edit', compact('laboratory', 'userPermissions'));
    }

    /**
     * Update the specified laboratory in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Check if user has permission to edit laboratories
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanEdit) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit laboratories.'
            ]);
        }

        $laboratory = Laboratory::findOrFail($id);

        // Validate the request
        $validator = Validator::make($request->all(), [
            'laboratory_id' => 'required|string|max:50|unique:laboratories,laboratory_id,' . $id . ',laboratory_id',
            'laboratory_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'location' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:Available,Occupied,Under Maintenance'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ]);
        }

        try {
            DB::beginTransaction();

            // Update the laboratory
            $laboratory->update([
                'laboratory_id' => $request->laboratory_id,
                'laboratory_name' => $request->laboratory_name,
                'description' => $request->description,
                'location' => $request->location,
                'capacity' => $request->capacity,
                'status' => $request->status,
                'updated_by' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Laboratory updated successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating laboratory: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified laboratory from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Check if user has permission to delete laboratories
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanDelete) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete laboratories.'
            ]);
        }

        try {
            DB::beginTransaction();

            $laboratory = Laboratory::findOrFail($id);
            
            // Check if laboratory has active equipment or reservations
            if ($laboratory->equipment()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete laboratory. Please remove or transfer all equipment first.'
                ]);
            }

            if ($laboratory->activeReservations()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete laboratory. There are active reservations.'
                ]);
            }

            // Soft delete the laboratory
            $laboratory->update(['deleted_by' => Auth::id()]);
            $laboratory->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Laboratory deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting laboratory: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Restore a soft-deleted laboratory.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        // Check if user has permission to edit laboratories
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanEdit) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to restore laboratories.'
            ]);
        }

        try {
            DB::beginTransaction();

            $laboratory = Laboratory::withTrashed()->findOrFail($id);
            $laboratory->update(['restored_by' => Auth::id()]);
            $laboratory->restore();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Laboratory restored successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error restoring laboratory: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get the permissions for Laboratory Management module.
     *
     * @return \App\Models\RolePolicy|null
     */
    public function getUserPermissions($moduleName)
    {
        return parent::getUserPermissions($moduleName);
    }
} 