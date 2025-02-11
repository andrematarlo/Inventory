<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index()
    {
        try {
            $activeEmployees = Employee::where('IsDeleted', false)
                ->with(['userAccount', 'createdBy', 'modifiedBy'])
                ->get();

            // Debug each employee's relationships
            foreach ($activeEmployees as $employee) {
                \Log::info('Employee Details:', [
                    'employee_id' => $employee->EmployeeID,
                    'employee_name' => $employee->FirstName . ' ' . $employee->LastName,
                    'created_by_id' => $employee->CreatedById,
                    'modified_by_id' => $employee->ModifiedById,
                    'created_by_relation' => $employee->createdBy ? [
                        'id' => $employee->createdBy->EmployeeID,
                        'name' => $employee->createdBy->FirstName . ' ' . $employee->createdBy->LastName
                    ] : null,
                    'modified_by_relation' => $employee->modifiedBy ? [
                        'id' => $employee->modifiedBy->EmployeeID,
                        'name' => $employee->modifiedBy->FirstName . ' ' . $employee->modifiedBy->LastName
                    ] : null
                ]);
            }

            $deletedEmployees = Employee::where('IsDeleted', true)
                ->with(['userAccount', 'createdBy', 'modifiedBy', 'deletedBy'])
                ->orderBy('LastName')
                ->get();

            return view('employees.index', [
                'activeEmployees' => $activeEmployees,
                'deletedEmployees' => $deletedEmployees
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading employees: ' . $e->getMessage());
            return back()->with('error', 'Error loading employees: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $roles = Role::where('IsDeleted', 0)
                    ->orderBy('RoleName')
                    ->pluck('RoleName', 'RoleId');

        return view('employees.create', compact('roles'));
    }

    public function store(Request $request)
    {
        try {
            // First check if user is authenticated
            if (!Auth::check()) {
                \Log::error('User not authenticated');
                return redirect()->route('login');
            }

            // Get authenticated user's UserAccount
            $userAccount = Auth::user();
            if (!$userAccount) {
                \Log::error('UserAccount not found');
                return redirect()->back()->with('error', 'User account not found.');
            }

            \Log::info('Authenticated user:', [
                'user_account_id' => $userAccount->UserAccountID,
                'username' => $userAccount->Username
            ]);

            // Check if current user is admin
            $currentEmployee = Employee::where('UserAccountID', $userAccount->UserAccountID)
                ->where('IsDeleted', false)
                ->first();
                
            if (!$currentEmployee) {
                \Log::error('Current employee not found:', [
                    'user_account_id' => $userAccount->UserAccountID
                ]);
                return redirect()->back()->with('error', 'Employee record not found for current user.');
            }

            if (!str_contains($currentEmployee->Role, 'Admin')) {
                return redirect()->back()->with('error', 'Only administrators can add employees.');
            }

            // Log current employee info
            \Log::info('Current employee creating new employee:', [
                'creator_id' => $currentEmployee->EmployeeID,
                'creator_name' => $currentEmployee->FirstName . ' ' . $currentEmployee->LastName,
                'creator_role' => $currentEmployee->Role
            ]);

            $request->validate([
                'FirstName' => 'required|string|max:255',
                'LastName' => 'required|string|max:255',
                'Email' => 'required|email|max:255',
                'Gender' => 'required|in:Male,Female',
                'Address' => 'required|string',
                'Username' => 'required|string|unique:useraccount,Username',
                'Password' => 'required|string|min:6',
                'roles' => 'required|array|min:1',
                'roles.*' => 'required|exists:roles,RoleId'
            ]);

            DB::beginTransaction();

            try {
                // Get the current authenticated employee
                $currentEmployee = Employee::where('UserAccountID', auth()->user()->UserAccountID)
                    ->where('IsDeleted', false)
                    ->first();

                if (!$currentEmployee) {
                    throw new \Exception('Current employee record not found');
                }

                // Log the creator's information
                \Log::info('Creating Employee - Creator Info:', [
                    'creator_employee_id' => $currentEmployee->EmployeeID,
                    'creator_user_account_id' => auth()->user()->UserAccountID,
                    'creator_name' => $currentEmployee->FirstName . ' ' . $currentEmployee->LastName
                ]);

                // Get role names for the selected role IDs
                $roleNames = Role::whereIn('RoleId', $request->roles)
                    ->pluck('RoleName')
                    ->implode(', ');

                // Create user account first
                $newUserAccount = UserAccount::create([
                    'Username' => $request->Username,
                    'Password' => Hash::make($request->Password),
                    'role' => $roleNames,
                    'IsDeleted' => false,
                    'DateCreated' => now(),
                    'CreatedById' => $currentEmployee->EmployeeID
                ]);

                // Create employee record
                $employee = Employee::create([
                    'FirstName' => $request->FirstName,
                    'LastName' => $request->LastName,
                    'Email' => $request->Email,
                    'Gender' => $request->Gender,
                    'Address' => $request->Address,
                    'UserAccountID' => $newUserAccount->UserAccountID,
                    'Role' => $roleNames,
                    'IsDeleted' => false,
                    'DateCreated' => now(),
                    'CreatedById' => $currentEmployee->EmployeeID,
                    'ModifiedById' => $currentEmployee->EmployeeID
                ]);

                // Add debug logging
                \Log::info('New Employee Created:', [
                    'employee_id' => $employee->EmployeeID,
                    'created_by_id' => $employee->CreatedById,
                    'modified_by_id' => $employee->ModifiedById,
                    'creator_employee' => $currentEmployee->toArray()
                ]);

                // Attach roles to the employee in the pivot table
                foreach ($request->roles as $roleId) {
                    DB::table('employee_roles')->insert([
                        'EmployeeId' => $employee->EmployeeID,
                        'RoleId' => $roleId,
                        'IsDeleted' => false,
                        'DateCreated' => now(),
                        'CreatedById' => $currentEmployee->EmployeeID
                    ]);
                }

                DB::commit();
                return redirect()->route('employees.index')->with('success', 'Employee created successfully');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error creating employee:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create employee: ' . $e->getMessage());
        }
    }

    public function edit($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $roles = [
            'Admin' => 'Admin',
            'InventoryStaff' => 'Inventory Staff',
            'InventoryManager' => 'Inventory Manager'
        ];

        return view('employees.edit', compact('employee', 'roles'));
    }

    public function update(Request $request, $id)
    {
        try {
            $employee = Employee::findOrFail($id);
            
            $request->validate([
                'FirstName' => 'required|string|max:255',
                'LastName' => 'required|string|max:255',
                'Email' => 'required|email|max:255',
                'Gender' => 'required|in:Male,Female',
                'Address' => 'required|string',
                'roles' => 'required|array|min:1',
                'roles.*' => 'required|exists:roles,RoleId'
            ]);

            DB::beginTransaction();

            try {
                // Get role names for the selected role IDs
                $roleNames = Role::whereIn('RoleId', $request->roles)
                    ->pluck('RoleName')
                    ->implode(', ');

                // Update employee
                $employee->update([
                    'FirstName' => $request->FirstName,
                    'LastName' => $request->LastName,
                    'Email' => $request->Email,
                    'Gender' => $request->Gender,
                    'Address' => $request->Address,
                    'Role' => $roleNames,
                    'DateModified' => now(),
                    'ModifiedById' => auth()->user()->UserAccountID
                ]);

                // Update UserAccount role
                if ($employee->UserAccountID) {
                    UserAccount::where('UserAccountID', $employee->UserAccountID)
                        ->update([
                            'role' => $roleNames,
                            'DateModified' => now(),
                            'ModifiedById' => auth()->user()->UserAccountID
                        ]);
                }

                // Update roles in pivot table
                DB::table('employee_roles')
                    ->where('EmployeeId', $employee->EmployeeID)
                    ->update(['IsDeleted' => true]); // Soft delete existing roles

                foreach ($request->roles as $roleId) {
                    DB::table('employee_roles')->insert([
                        'EmployeeId' => $employee->EmployeeID,
                        'RoleId' => $roleId,
                        'IsDeleted' => false,
                        'DateCreated' => now(),
                        'CreatedById' => auth()->user()->UserAccountID
                    ]);
                }

                DB::commit();
                return redirect()->route('employees.index')->with('success', 'Employee updated successfully');

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error updating employee: ' . $e->getMessage());
                return back()->with('error', 'Failed to update employee: ' . $e->getMessage())->withInput();
            }

        } catch (\Exception $e) {
            \Log::error('Error finding employee: ' . $e->getMessage());
            return back()->with('error', 'Employee not found.')->withInput();
        }
    }

    public function destroy($employeeId)
    {
        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($employeeId);
            
            $employee->update([
                'IsDeleted' => true,
                'DeletedByID' => auth()->user()->UserAccountID,
                'DateDeleted' => now()
            ]);

            if ($employee->userAccount) {
                $employee->userAccount->update([
                    'IsDeleted' => true,
                    'DeletedByID' => auth()->user()->UserAccountID,
                    'DateDeleted' => now()
                ]);
            }

            DB::commit();

            return redirect()->route('employees.index')
                ->with('success', 'Employee deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('employees.index')
                ->with('error', 'Failed to delete employee: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            DB::beginTransaction();

            $employee = Employee::where('EmployeeID', $id)
                ->where('IsDeleted', true)
                ->firstOrFail();

            // Update employee record
            $employee->update([
                'IsDeleted' => false,
                'RestoredByID' => auth()->user()->UserAccountID,
                'DateRestored' => now(),
                'DeletedByID' => null,
                'DateDeleted' => null,
                'ModifiedByID' => auth()->user()->UserAccountID,
                'DateModified' => now()
            ]);

            // Also restore associated user account if exists
            if ($employee->userAccount) {
                $employee->userAccount->update([
                    'IsDeleted' => false,
                    'RestoredByID' => auth()->user()->UserAccountID,
                    'DateRestored' => now(),
                    'DeletedByID' => null,
                    'DateDeleted' => null,
                    'ModifiedByID' => auth()->user()->UserAccountID,
                    'DateModified' => now()
                ]);
            }

            DB::commit();
            return redirect()->route('employees.index')
                ->with('success', 'Employee restored successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee restore failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to restore employee: ' . $e->getMessage());
        }
    }
} 