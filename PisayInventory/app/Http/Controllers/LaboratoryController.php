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

        // Get the user's role from the useraccount table
        $userRole = DB::table('useraccount')
            ->where('UserAccountID', Auth::id())
            ->value('role');

        // Debug: Show the role and user ID (remove after testing)
        dd($userRole, Auth::id());

        // Always filter by role except for Admin
        if ($userRole && $userRole !== 'Admin') {
            $laboratories = Laboratory::withTrashed()
                ->where('role', trim($userRole)) // trim to avoid space issues
                ->orderBy('laboratory_name')
                ->get();
        } else {
            // Admin can see all
            $laboratories = Laboratory::withTrashed()
                ->orderBy('laboratory_name')
                ->get();
        }

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

            // Get the user's role
            $userRole = DB::table('useraccount')
                ->where('UserAccountID', Auth::id())
                ->value('role');

            // Create the laboratory
            Laboratory::create([
                'laboratory_id' => $request->laboratory_id,
                'laboratory_name' => $request->laboratory_name,
                'description' => $request->description,
                'location' => $request->location,
                'capacity' => $request->capacity,
                'status' => $request->status,
                'created_by' => Auth::id(),
                'role' => $userRole
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

            // Get the user's role
            $userRole = DB::table('useraccount')
                ->where('UserAccountID', Auth::id())
                ->value('role');

            // Update the laboratory
            $laboratory->update([
                'laboratory_id' => $request->laboratory_id,
                'laboratory_name' => $request->laboratory_name,
                'description' => $request->description,
                'location' => $request->location,
                'capacity' => $request->capacity,
                'status' => $request->status,
                'updated_by' => Auth::id(),
                'role' => $userRole // Maintain the role
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
     * Restore the specified soft-deleted laboratory.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        try {
            // Find the laboratory including trashed (deleted) records
            $laboratory = Laboratory::withTrashed()->findOrFail($id);
            
            // Restore it
            $laboratory->restore();
            
            return response()->json([
                'success' => true,
                'message' => 'Laboratory restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore laboratory: ' . $e->getMessage()
            ], 500);
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

    /**
     * Display a listing of accountability forms.
     *
     * @return \Illuminate\Http\Response
     */
    public function accountability()
    {
        // Check if user has permission to view accountability forms
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view accountability forms.');
        }

        return view('laboratory.accountability.index');
    }

    /**
     * Show the form for creating a new accountability form.
     *
     * @return \Illuminate\Http\Response
     */
    public function createAccountability()
    {
        // Check if user has permission to add accountability forms
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return redirect()->route('laboratory.accountability')->with('error', 'You do not have permission to create accountability forms.');
        }

        return view('laboratory.accountability.create');
    }

    /**
     * Display the specified accountability form.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showAccountability($id)
    {
        // Check if user has permission to view accountability forms
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->route('laboratory.accountability')->with('error', 'You do not have permission to view accountability forms.');
        }

        return view('laboratory.accountability.show', compact('id'));
    }

    /**
     * Approve the specified accountability form.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approveAccountability($id)
    {
        // Check if user has permission to approve accountability forms
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanApprove) {
            return redirect()->route('laboratory.accountability')->with('error', 'You do not have permission to approve accountability forms.');
        }

        // Logic to approve accountability form
        return redirect()->route('laboratory.accountability')->with('success', 'Accountability form approved successfully.');
    }

    /**
     * Reject the specified accountability form.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function rejectAccountability($id)
    {
        // Check if user has permission to reject accountability forms
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanApprove) {
            return redirect()->route('laboratory.accountability')->with('error', 'You do not have permission to reject accountability forms.');
        }

        // Logic to reject accountability form
        return redirect()->route('laboratory.accountability')->with('success', 'Accountability form rejected successfully.');
    }

    /**
     * Delete the specified accountability form.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteAccountability($id)
    {
        // Check if user has permission to delete accountability forms
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanDelete) {
            return redirect()->route('laboratory.accountability')->with('error', 'You do not have permission to delete accountability forms.');
        }

        // Logic to delete accountability form
        return redirect()->route('laboratory.accountability')->with('success', 'Accountability form deleted successfully.');
    }

    /**
     * Display a listing of reagent requests.
     *
     * @return \Illuminate\Http\Response
     */
    public function reagent()
    {
        // Check if user has permission to view reagent requests
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view reagent requests.');
        }

        return view('laboratory.reagent.index');
    }

    /**
     * Show the form for creating a new reagent request.
     *
     * @return \Illuminate\Http\Response
     */
    public function createReagent()
    {
        // Check if user has permission to add reagent requests
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return redirect()->route('laboratory.reagent')->with('error', 'You do not have permission to create reagent requests.');
        }

        return view('laboratory.reagent.create');
    }

    /**
     * Display the specified reagent request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showReagent($id)
    {
        // Check if user has permission to view reagent requests
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->route('laboratory.reagent')->with('error', 'You do not have permission to view reagent requests.');
        }

        return view('laboratory.reagent.show', compact('id'));
    }

    /**
     * Approve the specified reagent request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approveReagent($id)
    {
        // Check if user has permission to approve reagent requests
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanApprove) {
            return redirect()->route('laboratory.reagent')->with('error', 'You do not have permission to approve reagent requests.');
        }

        // Logic to approve reagent request
        return redirect()->route('laboratory.reagent')->with('success', 'Reagent request approved successfully.');
    }

    /**
     * Reject the specified reagent request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function rejectReagent($id)
    {
        // Check if user has permission to reject reagent requests
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanApprove) {
            return redirect()->route('laboratory.reagent')->with('error', 'You do not have permission to reject reagent requests.');
        }

        // Logic to reject reagent request
        return redirect()->route('laboratory.reagent')->with('success', 'Reagent request rejected successfully.');
    }

    /**
     * Delete the specified reagent request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteReagent($id)
    {
        // Check if user has permission to delete reagent requests
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanDelete) {
            return redirect()->route('laboratory.reagent')->with('error', 'You do not have permission to delete reagent requests.');
        }

        // Logic to delete reagent request
        return redirect()->route('laboratory.reagent')->with('success', 'Reagent request deleted successfully.');
    }

    public function getUnitHead()
    {
        try {
            $unitHead = DB::table('employee')
                ->join('employee_roles', 'employee.EmployeeID', '=', 'employee_roles.EmployeeId')
                ->join('roles', 'employee_roles.RoleId', '=', 'roles.RoleId')
                ->where('employee.IsDeleted', 0)
                ->where('roles.RoleName', 'Unit Head')
                ->select('employee.*')
                ->first();

            if (!$unitHead) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Unit Head found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $unitHead
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving Unit Head: ' . $e->getMessage()
            ], 500);
        }
    }
} 