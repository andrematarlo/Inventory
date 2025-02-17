<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\RolePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class RoleController extends Controller
{
    private function getUserPermissions()
    {
        $userRole = auth()->user()->role;
        return RolePolicy::whereHas('role', function($query) use ($userRole) {
            $query->where('RoleName', $userRole);
        })->where('Module', 'Roles')->first();
    }

    public function index()
    {
        // Get user permissions
        $userPermissions = $this->getUserPermissions();
        
        // Check if user has View permission
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->back()->with('error', 'You do not have permission to view roles.');
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
                    'Module' => $module->ModuleName,  // Use ModuleName from modules table
                    'CanView' => true,
                    'CanAdd' => true,
                    'CanEdit' => true,
                    'CanDelete' => true,
                    'DateCreated' => now(),
                    'CreatedById' => auth()->id(),
                    'IsDeleted' => false
                ]);
            }

            return redirect()->route('roles.index')
                ->with('success', 'Role created successfully with all modules assigned');
        } catch (\Exception $e) {
            \Log::error('Role creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create role: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $role = Role::with('policies')->findOrFail($id);
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, $id)
    {
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

        // Update policies with null check
        if (!empty($request->policies) && is_array($request->policies)) {
            foreach ($request->policies as $policyId => $permissions) {
                $policy = RolePolicy::find($policyId);
                if ($policy) {
                    $policy->update([
                        'CanView' => isset($permissions['view']),
                        'CanAdd' => isset($permissions['add']),
                        'CanEdit' => isset($permissions['edit']),
                        'CanDelete' => isset($permissions['delete']),
                        'DateModified' => now(),
                        'ModifiedById' => Auth::id()
                    ]);
                }
            }
        }

        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        $role->IsDeleted = true;
        $role->DateDeleted = now();
        $role->DeletedById = Auth::id();
        $role->save();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully');
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
            return redirect()->route('roles.index')
                ->with('success', 'Role restored successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Role restore failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to restore role: ' . $e->getMessage());
        }
    }

    public function policies()
    {
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
                    // Check if policy exists
                    $policy = DB::table('role_policies as rp')
                        ->where('RoleId', $role->RoleId)
                        ->where('ModuleId', $module->ModuleId)
                        ->where('IsDeleted', false)
                        ->first();

                    // If no policy exists, create a default one
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
                            'CreatedById' => auth()->id(),
                            'IsDeleted' => false
                        ]);

                        $policy = DB::table('role_policies')
                            ->where('RolePolicyId', $policyId)
                            ->first();
                    }

                    // Create policy object with nested role
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

            return view('roles.policies', ['policies' => collect($allPolicies)]);
        } catch (\Exception $e) {
            \Log::error('Policy loading failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to load role policies: ' . $e->getMessage());
        }
    }

    public function updatePolicy(Request $request, $id)
    {
        $policy = RolePolicy::findOrFail($id);
        
        $policy->update([
            'CanView' => isset($request->permissions['view']),
            'CanAdd' => isset($request->permissions['add']),
            'CanEdit' => isset($request->permissions['edit']),
            'CanDelete' => isset($request->permissions['delete']),
            'DateModified' => now(),
            'ModifiedById' => Auth::id()
        ]);

        return redirect()->route('roles.policies')
            ->with('success', 'Role policy updated successfully');
    }
} 