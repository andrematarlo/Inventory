<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use App\Models\RolePolicy;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function getUserPermissions($module = null)
    {
        return parent::getUserPermissions('Roles');
    }

    public function checkModuleAccess($moduleName)
    {
        $userPermissions = parent::getUserPermissions($moduleName);
        
        if (!$userPermissions || !$userPermissions->CanView) {
            abort(403, 'You do not have permission to access this module.');
        }
        
        return $userPermissions;
    }

    public function index()
    {
        // Get user permissions
        $userPermissions = $this->getUserPermissions();
        
        // Check if user has View permission
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->route('dashboard')->with('sweet_alert', [
                'type' => 'error',
                'title' => 'Access Denied',
                'message' => 'You do not have permission to view roles.'
            ]);
        }

        $roles = Role::where('IsDeleted', false)
            ->with(['created_by_user', 'modified_by_user'])
            ->orderBy('DateCreated', 'desc')
            ->get();

        $trashedRoles = Role::where('IsDeleted', true)
            ->with(['deleted_by_user'])
            ->orderBy('DateDeleted', 'desc')
            ->get();

        return view('roles.index', compact('roles', 'trashedRoles', 'userPermissions'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        try {
            // 1. Create new role
            $roleId = DB::table('roles')->insertGetId([
                'RoleName' => $request->RoleName,
                'Description' => $request->Description,
                'DateCreated' => now(),
                'CreatedById' => auth()->id(),
                'IsDeleted' => false
            ]);

            // 2. Get all modules from modules table
            $modules = DB::table('modules')
                ->select('ModuleId', 'ModuleName')
                ->get();

            // 3. Automatically assign ALL modules to the new role
            foreach ($modules as $module) {
                DB::table('role_policies')->insert([
                    'RoleId' => $roleId,
                    'ModuleId' => $module->ModuleId,
                    'Module' => $module->ModuleName,
                    'CanView' => true,
                    'CanAdd' => true,
                    'CanEdit' => true,
                    'CanDelete' => true,
                    'DateCreated' => now(),
                    'CreatedById' => auth()->id(),
                    'IsDeleted' => false
                ]);
            }

            return redirect()->route('roles.index')->with('sweet_alert', [
                'type' => 'success',
                'title' => 'Success',
                'message' => 'Role created successfully with all modules assigned'
            ]);
        } catch (\Exception $e) {
            \Log::error('Role creation failed: ' . $e->getMessage());
            return redirect()->back()->with('sweet_alert', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Failed to create role: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function edit($id)
    {
        $userPermissions = $this->getUserPermissions();
        
        if (!$userPermissions || !$userPermissions->CanEdit) {
            return redirect()->back()->with('sweet_alert', [
                'type' => 'error',
                'title' => 'Access Denied',
                'message' => 'You do not have permission to edit roles.'
            ]);
        }
        
        $role = Role::with('policies')->findOrFail($id);
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, $id)
    {
        $userPermissions = $this->getUserPermissions();
        
        if (!$userPermissions || !$userPermissions->CanEdit) {
            return redirect()->back()->with('sweet_alert', [
                'type' => 'error',
                'title' => 'Access Denied',
                'message' => 'You do not have permission to update roles.'
            ]);
        }
        
        $role = Role::findOrFail($id);
        
        $request->validate([
            'RoleName' => 'required|unique:roles,RoleName,' . $id . ',RoleId',
            'Description' => 'nullable'
        ]);

        $role->RoleName = $request->RoleName;
        $role->Description = $request->Description;
        $role->DateModified = now();
        $role->ModifiedById = Auth::id();
        $role->save();

        // Update policies
        if (!empty($request->policies) && is_array($request->policies)) {
            foreach ($request->policies as $policyId => $permissions) {
                $policy = RolePolicy::find($policyId);
                if ($policy) {
                    $policy->update([
                        'CanView' => isset($permissions['view']) && $permissions['view'] == "1",
                        'CanAdd' => isset($permissions['add']) && $permissions['add'] == "1",
                        'CanEdit' => isset($permissions['edit']) && $permissions['edit'] == "1",
                        'CanDelete' => isset($permissions['delete']) && $permissions['delete'] == "1",
                        'DateModified' => now(),
                        'ModifiedById' => Auth::id()
                    ]);
                }
            }
        }

        return redirect()->route('roles.index')->with('sweet_alert', [
            'type' => 'success',
            'title' => 'Success',
            'message' => 'Role updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        $role->IsDeleted = true;
        $role->DateDeleted = now();
        $role->DeletedById = Auth::id();
        $role->save();

        return redirect()->route('roles.index')->with('sweet_alert', [
            'type' => 'success',
            'title' => 'Success',
            'message' => 'Role deleted successfully'
        ]);
    }

    public function restore($id)
    {
        try {
            DB::beginTransaction();
            
            $role = Role::findOrFail($id);
            $role->update([
                'IsDeleted' => false,
                'DeletedById' => null,
                'DateDeleted' => null,
                'RestoredById' => Auth::id(),
                'DateRestored' => now(),
                'ModifiedById' => null,
                'DateModified' => null
            ]);

            DB::commit();
            return redirect()->route('roles.index')->with('sweet_alert', [
                'type' => 'success',
                'title' => 'Success',
                'message' => 'Role restored successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Role restore failed: ' . $e->getMessage());
            return back()->with('sweet_alert', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Failed to restore role: ' . $e->getMessage()
            ]);
        }
    }

    public function policies()
    {
        // Add permission checking
        $userPermissions = $this->getUserPermissions();
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->route('dashboard')->with('sweet_alert', [
                'type' => 'error',
                'title' => 'Access Denied',
                'message' => 'You do not have permission to view role policies.'
            ]);
        }

        try {
            // Get all active roles
            $roles = DB::table('roles')
                ->where('IsDeleted', false)
                ->get();

            // Get all modules
            $modules = DB::table('modules')->get();

            // Initialize array to store all policies
            $allPolicies = [];

            // For each role and module combination, get or create policy
            foreach ($roles as $role) {
                foreach ($modules as $module) {
                    // Check if policy exists using both RoleId and ModuleId
                    $policy = DB::table('role_policies')
                        ->where('RoleId', $role->RoleId)
                        ->where('Module', $module->ModuleName)
                        ->where('IsDeleted', false)
                        ->first();

                    // If no policy exists, create a default one
                    if (!$policy) {
                        // Begin transaction to prevent race conditions
                        DB::beginTransaction();
                        try {
                            // Check again within transaction to prevent duplicates
                            $policy = DB::table('role_policies')
                                ->where('RoleId', $role->RoleId)
                                ->where('Module', $module->ModuleName)
                                ->where('IsDeleted', false)
                                ->lockForUpdate()
                                ->first();

                            if (!$policy) {
                                $policyId = DB::table('role_policies')->insertGetId([
                                    'RoleId' => $role->RoleId,
                                    'ModuleId' => $module->ModuleId,
                                    'Module' => $module->ModuleName,
                                    'CanView' => true,
                                    'CanAdd' => true,
                                    'CanEdit' => true,
                                    'CanDelete' => true,
                                    'DateCreated' => now(),
                                    'CreatedById' => Auth::id(),
                                    'IsDeleted' => false
                                ]);

                                $policy = DB::table('role_policies')
                                    ->where('RolePolicyId', $policyId)
                                    ->first();
                            }
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            throw $e;
                        }
                    }

                    // Create policy object with nested role
                    if ($policy) {
                        $policyObj = (object)[
                            'RolePolicyId' => $policy->RolePolicyId,
                            'RoleId' => $role->RoleId,
                            'ModuleId' => $module->ModuleId,
                            'Module' => $module->ModuleName,
                            'CanView' => $policy->CanView ?? true,
                            'CanAdd' => $policy->CanAdd ?? true,
                            'CanEdit' => $policy->CanEdit ?? true,
                            'CanDelete' => $policy->CanDelete ?? true,
                            'role' => (object)[
                                'RoleId' => $role->RoleId,
                                'RoleName' => $role->RoleName
                            ]
                        ];

                        $allPolicies[] = $policyObj;
                    }
                }
            }

            // Remove any duplicate policies
            $uniquePolicies = collect($allPolicies)->unique(function ($policy) {
                return $policy->RoleId . '-' . $policy->Module;
            })->values();

            return view('roles.policies', [
                'policies' => $uniquePolicies,
                'userPermissions' => $userPermissions
            ]);
        } catch (\Exception $e) {
            Log::error('Policy loading failed: ' . $e->getMessage());
            return redirect()->back()->with('sweet_alert', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Failed to load role policies: ' . $e->getMessage()
            ]);
        }
    }

    public function updatePolicy(Request $request, $id)
    {
        $userPermissions = $this->getUserPermissions();
        if (!$userPermissions || !$userPermissions->CanEdit) {
            return redirect()->back()->with('sweet_alert', [
                'type' => 'error',
                'title' => 'Access Denied',
                'message' => 'You do not have permission to update role policies.'
            ]);
        }

        $policy = RolePolicy::findOrFail($id);
        
        $policy->update([
            'CanView' => isset($request->permissions['view']),
            'CanAdd' => isset($request->permissions['add']),
            'CanEdit' => isset($request->permissions['edit']),
            'CanDelete' => isset($request->permissions['delete']),
            'DateModified' => now(),
            'ModifiedById' => Auth::id()
        ]);

        return redirect()->route('roles.policies')->with('sweet_alert', [
            'type' => 'success',
            'title' => 'Success',
            'message' => 'Role policy updated successfully'
        ]);
    }

    public function createPolicy(Request $request)
    {
        // Check permissions
        $userPermissions = $this->getUserPermissions();
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return redirect()->back()->with('sweet_alert', [
                'type' => 'error',
                'title' => 'Access Denied',
                'message' => 'You do not have permission to create role policies.'
            ]);
        }

        try {
            DB::beginTransaction();

            // Check if policy already exists
            $existingPolicy = RolePolicy::where('RoleId', $request->RoleId)
                ->where('Module', $request->Module)
                ->where('IsDeleted', false)
                ->first();

            if ($existingPolicy) {
                return redirect()->back()->with('sweet_alert', [
                    'type' => 'error',
                    'title' => 'Error',
                    'message' => 'A policy for this role and module already exists.'
                ]);
            }

            // Get the module ID
            $module = DB::table('modules')
                ->where('ModuleName', $request->Module)
                ->first();

            if (!$module) {
                // Create the module if it doesn't exist
                $moduleId = DB::table('modules')->insertGetId([
                    'ModuleName' => $request->Module,
                    'CreatedAt' => now(),
                    'UpdatedAt' => now()
                ]);
            } else {
                $moduleId = $module->ModuleId;
            }

            // Create new policy
            $policy = new RolePolicy();
            $policy->RoleId = $request->RoleId;
            $policy->Module = $request->Module;
            $policy->ModuleId = $moduleId;
            $policy->CanView = $request->has('CanView');
            $policy->CanAdd = $request->has('CanAdd');
            $policy->CanEdit = $request->has('CanEdit');
            $policy->CanDelete = $request->has('CanDelete');
            $policy->DateCreated = now();
            $policy->CreatedById = Auth::id();
            $policy->IsDeleted = false;
            $policy->save();

            DB::commit();

            return redirect()->route('roles.policies')->with('sweet_alert', [
                'type' => 'success',
                'title' => 'Success',
                'message' => 'Role policy created successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Policy creation failed: ' . $e->getMessage());
            return redirect()->back()->with('sweet_alert', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Failed to create policy: ' . $e->getMessage()
            ])->withInput();
        }
    }
} 