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
        $validated = $request->validate([
            'RoleName' => 'required|string|max:255|unique:roles,RoleName',
            'Description' => 'nullable|string'
        ]);

        $role = new Role();
        $role->RoleName = $validated['RoleName'];
        $role->Description = $validated['Description'];
        $role->DateCreated = now();
        $role->CreatedById = auth()->id();
        $role->IsDeleted = false;
        $role->save();

        // Create default policies for the role
        $modules = ['Users', 'Roles', 'Inventory', 'Classifications']; // Add your modules
        foreach ($modules as $module) {
            RolePolicy::create([
                'RoleId' => $role->RoleId,
                'Module' => $module,
                'CanView' => false,
                'CanAdd' => false,
                'CanEdit' => false,
                'CanDelete' => false,
                'DateCreated' => now(),
                'CreatedById' => Auth::id()
            ]);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully');
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
            $policies = RolePolicy::orderBy('RoleId')
                ->orderBy('Module')
                ->get();

            \Log::info('Policies query result:', ['count' => $policies->count()]);
            
            return view('roles.policies', compact('policies'));
        } catch (\Exception $e) {
            \Log::error('Error in policies method:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error loading policies: ' . $e->getMessage());
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