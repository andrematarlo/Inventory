<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);
            
            // Check if role is being used
            if ($role->users()->count() > 0) {
                return back()->with('error', 'Cannot delete role because it is being used by ' . $role->users()->count() . ' users.');
            }

            $role->delete();
            
            // Just redirect back without any message
            return redirect()->back();
            
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while deleting the role.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->update($request->all());
            
            // Just redirect back without any message
            return redirect()->back();
            
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while updating the role.');
        }
    }

    public function restore($id)
    {
        try {
            $role = Role::withTrashed()->findOrFail($id);
            $role->restore();
            
            // Just redirect back without any message
            return redirect()->back();
            
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while restoring the role.');
        }
    }

    public function edit($id)
    {
        try {
            $role = Role::findOrFail($id);
            $userPermissions = $this->getUserPermissions();
            
            return view('roles.edit', [
                'role' => $role,
                'userPermissions' => $userPermissions
            ]);
            
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while loading the role.');
        }
    }
} 