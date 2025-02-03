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
        $employees = Employee::with('userAccount')
            ->get();
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $roles = Role::where('IsDeleted', false)->get();
        return view('employees.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'FirstName' => 'required|string|max:100',
            'LastName' => 'required|string|max:100',
            'Email' => 'required|email|unique:employee,Email',
            'Gender' => 'required|in:Male,Female',
            'Address' => 'required|string',
            'Username' => 'required|unique:useraccount,Username',
            'Password' => 'required|min:8|confirmed',
            'RoleId' => 'required|exists:roles,RoleId'
        ]);

        DB::beginTransaction();
        try {
            $role = Role::findOrFail($validated['RoleId']);
            $now = now();

            // Create UserAccount
            $userAccountId = DB::table('useraccount')->insertGetId([
                'Username' => $validated['Username'],
                'Password' => Hash::make($validated['Password']),
                'role' => substr($role->RoleName, 0, 50), // Ensure it doesn't exceed 50 chars
                'DateCreated' => $now,
                'CreatedById' => Auth::id(),
                'IsDeleted' => 0
            ]);

            // Create Employee
            DB::table('employee')->insert([
                'UserAccountID' => $userAccountId,
                'FirstName' => $validated['FirstName'],
                'LastName' => $validated['LastName'],
                'Email' => $validated['Email'],
                'Gender' => $validated['Gender'],
                'Address' => $validated['Address'],
                'Role' => substr($role->RoleName, 0, 50), // Ensure it doesn't exceed 50 chars
                'DateCreated' => $now,
                'CreatedById' => Auth::id(),
                'IsDeleted' => 0
            ]);

            DB::commit();
            return redirect()->route('employees.index')
                           ->with('success', 'Employee added successfully');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create employee', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create employee: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $employee = Employee::with('userAccount')
            ->where('EmployeeID', $id)
            ->firstOrFail();
        $roles = Role::where('IsDeleted', false)->get();
        return view('employees.edit', compact('employee', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::with('userAccount')
            ->where('EmployeeID', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'FirstName' => 'required|string|max:100',
            'LastName' => 'required|string|max:100',
            'Email' => 'required|email|unique:employee,Email,' . $id . ',EmployeeID',
            'Gender' => 'required|in:Male,Female',
            'Address' => 'required|string',
            'Username' => 'required|unique:useraccount,Username,' . $employee->userAccount->UserAccountID . ',UserAccountID',
            'RoleId' => 'required|exists:roles,RoleId',
            'Password' => 'nullable|min:8|confirmed'
        ]);

        DB::beginTransaction();
        try {
            $role = Role::findOrFail($validated['RoleId']);
            $now = now();

            // Update employee record
            DB::table('employee')
                ->where('EmployeeID', $id)
                ->update([
                    'FirstName' => $validated['FirstName'],
                    'LastName' => $validated['LastName'],
                    'Email' => $validated['Email'],
                    'Gender' => $validated['Gender'],
                    'Address' => $validated['Address'],
                    'Role' => substr($role->RoleName, 0, 50), // Ensure it doesn't exceed 50 chars
                    'DateModified' => $now,
                    'ModifiedById' => Auth::id()
                ]);

            // Update user account
            $updateData = [
                'Username' => $validated['Username'],
                'role' => substr($role->RoleName, 0, 50), // Ensure it doesn't exceed 50 chars
                'DateModified' => $now,
                'ModifiedById' => Auth::id()
            ];

            if ($request->filled('Password')) {
                $updateData['Password'] = Hash::make($validated['Password']);
            }

            DB::table('useraccount')
                ->where('UserAccountID', $employee->userAccount->UserAccountID)
                ->update($updateData);

            DB::commit();
            return redirect()->route('employees.index')
                           ->with('success', 'Employee updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Employee Update Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return back()->withInput()
                        ->withErrors(['error' => 'Failed to update employee. ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $employee = Employee::with('userAccount')->findOrFail($id);
            
            // Update employee record
            $employee->update([
                'IsDeleted' => true,
                'DateDeleted' => now(),
                'DeletedById' => Auth::id()
            ]);

            // Update user account
            $employee->userAccount->update([
                'IsDeleted' => true,
                'DateDeleted' => now(),
                'DeletedById' => Auth::id()
            ]);

            DB::commit();
            return redirect()->route('employees.index')
                ->with('success', 'Employee deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to delete employee. ' . $e->getMessage()]);
        }
    }

    public function restore($id)
    {
        DB::beginTransaction();
        try {
            // Find the employee without withTrashed since we're using IsDeleted flag
            $employee = Employee::with('userAccount')
                ->where('EmployeeID', $id)
                ->first();

            if (!$employee) {
                throw new \Exception('Employee not found');
            }

            // Debug logging
            Log::info('Restoring Employee', [
                'employee_id' => $id,
                'employee' => $employee->toArray()
            ]);

            // Restore employee record
            DB::table('employee')
                ->where('EmployeeID', $id)
                ->update([
                    'IsDeleted' => false,
                    'DateDeleted' => null,
                    'DeletedById' => null,
                    'DateModified' => now(),
                    'ModifiedById' => Auth::id()
                ]);

            // Restore user account
            DB::table('useraccount')
                ->where('UserAccountID', $employee->UserAccountID)
                ->update([
                    'IsDeleted' => false,
                    'DateDeleted' => null,
                    'DeletedById' => null,
                    'DateModified' => now(),
                    'ModifiedById' => Auth::id()
                ]);

            // Debug logging after restore
            Log::info('Employee Restored', [
                'employee' => DB::table('employee')->where('EmployeeID', $id)->first(),
                'user_account' => DB::table('useraccount')->where('UserAccountID', $employee->UserAccountID)->first()
            ]);

            DB::commit();
            return redirect()->route('employees.index')
                ->with('success', 'Employee restored successfully');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Employee Restore Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to restore employee. ' . $e->getMessage()]);
        }
    }
} 