<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RolePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::where('IsDeleted', false)
            ->orderBy('DateCreated', 'desc')
            ->get();
        $trashedRoles = Role::where('IsDeleted', true)
            ->orderBy('DateDeleted', 'desc')
            ->get();
        return view('roles.index', compact('roles', 'trashedRoles'));
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
        $role = Role::findOrFail($id);
        $role->IsDeleted = false;
        $role->DateDeleted = null;
        $role->DeletedById = null;
        $role->DateModified = now();
        $role->ModifiedById = Auth::id();
        $role->save();

        return redirect()->route('roles.index')
            ->with('success', 'Role restored successfully');
    }

    public function policies()
    {
        $policies = RolePolicy::with('role')
            ->whereHas('role', function($query) {
                $query->where('IsDeleted', false);
            })
            ->orderBy('RoleId')
            ->orderBy('Module')
            ->get() ?? collect(); // Ensure we always have a collection

        return view('roles.policies', compact('policies'));
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