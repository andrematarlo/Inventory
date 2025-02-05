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
            $employees = Employee::with(['userAccount'])->where('IsDeleted', 0)->get();
            
            // Debug each employee safely
            foreach($employees as $employee) {
                \Log::info('Employee Debug:', [
                    'EmployeeID' => $employee->EmployeeID ?? 'No ID',
                    'UserAccountID' => $employee->UserAccountID ?? 'No UserAccountID',
                    'Has UserAccount?' => isset($employee->userAccount) ? 'Yes' : 'No',
                    'Username' => $employee->userAccount->Username ?? 'No Username',
                    'Raw Employee' => $employee->toArray()
                ]);
            }

            $trashedEmployees = Employee::with(['userAccount'])
                ->where('IsDeleted', 1)
                ->get();

            $roles = [
                'Admin' => 'Admin',
                'InventoryStaff' => 'Inventory Staff',
                'InventoryManager' => 'Inventory Manager'
            ];

            return view('employees.index', compact('employees', 'trashedEmployees', 'roles'));

        } catch (\Exception $e) {
            \Log::error('Error in index:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('employees.index')->with('error', 'Error loading employees');
        }
    }

    public function create()
    {
        $roles = [
            'Admin' => 'Admin',
            'InventoryStaff' => 'Inventory Staff',
            'InventoryManager' => 'Inventory Manager'
        ];

        return view('employees.create', compact('roles'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'FirstName' => 'required|string|max:255',
                'LastName' => 'required|string|max:255',
                'Address' => 'nullable|string',
                'Email' => 'required|email|max:255|unique:employee,Email',
                'Gender' => 'required|string|in:Male,Female',
                'Role' => 'required|string|in:Admin,InventoryStaff,InventoryManager',
                'Username' => 'required|string|unique:useraccount,Username',
                'Password' => 'required|string|min:6'
            ]);

            DB::beginTransaction();

            // Let MySQL handle the UserAccountID
            $userAccount = new UserAccount();
            $userAccount->Username = $request->Username;
            $userAccount->Password = bcrypt($request->Password);
            $userAccount->role = $request->Role;
            $userAccount->CreatedById = auth()->user()->UserAccountID;
            $userAccount->DateCreated = now();
            $userAccount->ModifiedById = auth()->user()->UserAccountID;
            $userAccount->DateModified = now();
            $userAccount->IsDeleted = false;
            $userAccount->save();

            if (!$userAccount->UserAccountID) {
                throw new \Exception('UserAccount creation failed');
            }

            \Log::info('UserAccount created:', [
                'ID' => $userAccount->UserAccountID,
                'Username' => $userAccount->Username
            ]);

            // Get the last EmployeeID
            $lastEmployee = Employee::orderBy('EmployeeID', 'desc')->first();
            $nextEmployeeID = $lastEmployee ? ($lastEmployee->EmployeeID + 1) : 1;

            // Create employee
            $employee = Employee::create([
                'EmployeeID' => $nextEmployeeID,
                'UserAccountID' => $userAccount->UserAccountID,
                'FirstName' => $request->FirstName,
                'LastName' => $request->LastName,
                'Address' => $request->Address,
                'Email' => $request->Email,
                'Gender' => $request->Gender,
                'Role' => $request->Role,
                'CreatedById' => auth()->user()->UserAccountID,
                'DateCreated' => now(),
                'ModifiedById' => auth()->user()->UserAccountID,
                'DateModified' => now(),
                'IsDeleted' => false
            ]);

            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Employee added successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in store:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('employees.index')
                ->with('error', 'Failed to add employee: ' . $e->getMessage());
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

    public function update(Request $request, $employeeId)
    {
        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($employeeId);

            $request->validate([
                'FirstName' => 'required|string|max:255',
                'LastName' => 'required|string|max:255',
                'Address' => 'nullable|string',
                'Email' => 'required|email|max:255|unique:employee,Email,' . $employeeId . ',EmployeeID',
                'Gender' => 'required|string|in:Male,Female',
                'Role' => 'required|string|in:Admin,InventoryStaff,InventoryManager',
            ]);

            $employee->update([
                'FirstName' => $request->FirstName,
                'LastName' => $request->LastName,
                'Address' => $request->Address,
                'Email' => $request->Email,
                'Gender' => $request->Gender,
                'Role' => $request->Role,
                'ModifiedById' => auth()->user()->UserAccountID,
                'DateModified' => now(),
            ]);

            if ($employee->userAccount) {
                $employee->userAccount->update([
                    'Role' => $request->Role,
                    'ModifiedById' => auth()->user()->UserAccountID,
                    'DateModified' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('employees.index')
                ->with('success', 'Employee updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating employee:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('employees.index')
                ->with('error', 'Failed to update employee: ' . $e->getMessage());
        }
    }

    public function destroy($employeeId)
    {
        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($employeeId);
            
            $employee->update([
                'IsDeleted' => true,
                'DeletedById' => auth()->user()->UserAccountID,
                'DateDeleted' => now()
            ]);

            if ($employee->userAccount) {
                $employee->userAccount->update([
                    'IsDeleted' => true,
                    'DeletedById' => auth()->user()->UserAccountID,
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

    public function restore($employeeId)
    {
        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($employeeId);
            
            $employee->update([
                'IsDeleted' => false,
                'DeletedById' => null,
                'DateDeleted' => null
            ]);

            if ($employee->userAccount) {
                $employee->userAccount->update([
                    'IsDeleted' => false,
                    'DeletedById' => null,
                    'DateDeleted' => null
                ]);
            }

            DB::commit();

            return redirect()->route('employees.index')
                ->with('success', 'Employee restored successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('employees.index')
                ->with('error', 'Failed to restore employee: ' . $e->getMessage());
        }
    }
} 