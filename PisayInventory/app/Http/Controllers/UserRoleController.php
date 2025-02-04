<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserRole;
use App\Models\Role;
use App\Models\User;

class UserRoleController extends Controller
{
    public function index()
    {
        try {
            $userRoles = UserRole::where('IsDeleted', false)
                        ->orderBy('DateCreated', 'desc')
                        ->get();
            
            $roles = Role::where('IsDeleted', false)->get();
            $users = User::where('IsDeleted', false)->get();

            return view('user-roles.index', compact('userRoles', 'roles', 'users'));
        } catch (\Exception $e) {
            \Log::error('Error in UserRoleController@index: ' . $e->getMessage());
            return back()->with('error', 'Error loading user roles: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $users = User::where('IsDeleted', false)->get();
        $roles = Role::where('IsDeleted', false)->get();
        return view('user-roles.create', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'UserAccountId' => 'required|exists:user_accounts,UserAccountId',
                'RoleId' => 'required|exists:roles,RoleId'
            ]);

            $userRole = new UserRole();
            $userRole->UserAccountId = $request->UserAccountId;
            $userRole->RoleId = $request->RoleId;
            $userRole->DateCreated = now();
            $userRole->CreatedById = auth()->id();
            $userRole->IsDeleted = false;
            $userRole->save();

            return redirect()->route('user-roles.index')
                ->with('success', 'User role assigned successfully');
        } catch (\Exception $e) {
            \Log::error('Error in UserRoleController@store: ' . $e->getMessage());
            return redirect()->route('user-roles.index')
                ->with('error', 'Error assigning user role: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $userRole = UserRole::findOrFail($id);
        $users = User::where('IsDeleted', false)->get();
        $roles = Role::where('IsDeleted', false)->get();
        return view('user-roles.edit', compact('userRole', 'users', 'roles'));
    }

    public function update(Request $request, $id)
    {
        try {
            $userRole = UserRole::findOrFail($id);

            $request->validate([
                'UserAccountId' => 'required|exists:user_accounts,UserAccountId',
                'RoleId' => 'required|exists:roles,RoleId'
            ]);

            $userRole->UserAccountId = $request->UserAccountId;
            $userRole->RoleId = $request->RoleId;
            $userRole->DateModified = now();
            $userRole->ModifiedById = auth()->id();
            $userRole->save();

            return redirect()->route('user-roles.index')
                ->with('success', 'User role updated successfully');
        } catch (\Exception $e) {
            \Log::error('Error in UserRoleController@update: ' . $e->getMessage());
            return redirect()->route('user-roles.index')
                ->with('error', 'Error updating user role: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $userRole = UserRole::findOrFail($id);
            $userRole->IsDeleted = true;
            $userRole->DateDeleted = now();
            $userRole->DeletedById = auth()->id();
            $userRole->save();

            return redirect()->route('user-roles.index')
                ->with('success', 'User role deleted successfully');
        } catch (\Exception $e) {
            \Log::error('Error in UserRoleController@destroy: ' . $e->getMessage());
            return redirect()->route('user-roles.index')
                ->with('error', 'Error deleting user role: ' . $e->getMessage());
        }
    }
} 