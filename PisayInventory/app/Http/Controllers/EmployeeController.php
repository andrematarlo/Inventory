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
            // Enable query logging for debugging
            \DB::enableQueryLog();

            // Optimize query with specific selects and joins
            $employees = Employee::select(
                'employee.*',
                'ua.Username',
                'cb.FirstName as CreatedByFirstName',
                'cb.LastName as CreatedByLastName',
                'mb.FirstName as ModifiedByFirstName',
                'mb.LastName as ModifiedByLastName'
            )
            ->leftJoin('useraccount as ua', function($join) {
                $join->on('employee.UserAccountID', '=', 'ua.UserAccountID');
            })
            ->leftJoin('employee as cb', function($join) {
                $join->on('employee.CreatedByID', '=', 'cb.EmployeeID');
            })
            ->leftJoin('employee as mb', function($join) {
                $join->on('employee.ModifiedByID', '=', 'mb.EmployeeID');
            })
            ->where('employee.IsDeleted', 0)
            ->orderBy('employee.LastName')
            ->paginate(25);

            // Log the executed query for debugging
            \Log::info('Employee Query:', [
                'query' => \DB::getQueryLog()
            ]);

            // Only load trashed employees if specifically requested
            $trashedEmployees = null;
            if (request()->has('showTrashed')) {
                $trashedEmployees = Employee::with(['userAccount'])
                    ->where('IsDeleted', 1)
                    ->orderBy('LastName')
                    ->paginate(25);
            }

            $roles = [
                'Admin' => 'Admin',
                'InventoryStaff' => 'Inventory Staff',
                'InventoryManager' => 'Inventory Manager'
            ];

            return view('employees.index', compact('employees', 'trashedEmployees', 'roles'));

        } catch (\Exception $e) {
            \Log::error('Error in employee index:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Error loading employees: ' . $e->getMessage());
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
            // Check if current user is admin
            $currentEmployee = Employee::where('UserAccountID', auth()->user()->UserAccountID)
                ->where('IsDeleted', false)
                ->first();
                
            if (!$currentEmployee || $currentEmployee->Role !== 'Admin') {
                return redirect()->back()->with('error', 'Only administrators can add employees.');
            }

            $request->validate([
                'FirstName' => 'required|string|max:255',
                'LastName' => 'required|string|max:255',
                'Email' => 'required|email|max:255',
                'Gender' => 'required|in:Male,Female',
                'Address' => 'required|string',
                'Username' => 'required|string|unique:useraccount,Username',
                'Password' => 'required|string|min:6',
                'roles' => 'required|array|min:1',
                'roles.*' => 'required|string|in:Admin,InventoryStaff,InventoryManager'
            ]);

            DB::beginTransaction();

            // Create user account first
            $userAccount = UserAccount::create([
                'Username' => $request->Username,
                'Password' => Hash::make($request->Password),
                'CreatedById' => auth()->user()->UserAccountID,
                'DateCreated' => now(),
                'IsDeleted' => false
            ]);

            // Get the next EmployeeID
            $lastEmployee = Employee::orderBy('EmployeeID', 'desc')->first();
            $nextEmployeeID = $lastEmployee ? $lastEmployee->EmployeeID + 1 : 1;

            // Create employee with multiple roles
            $employee = Employee::create([
                'EmployeeID' => $nextEmployeeID,
                'FirstName' => $request->FirstName,
                'LastName' => $request->LastName,
                'Email' => $request->Email,
                'Gender' => $request->Gender,
                'Address' => $request->Address,
                'UserAccountID' => $userAccount->UserAccountID,
                'Role' => implode(', ', $request->roles), // Add space after comma for better readability
                'CreatedByID' => auth()->user()->UserAccountID,
                'DateCreated' => now(),
                'IsDeleted' => false
            ]);

            DB::commit();

            return redirect()->route('employees.index')
                ->with('success', 'Employee created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating employee:', [
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

    public function update(Request $request, $employeeId)
    {
        try {
            // Check if current user is admin
            $currentEmployee = Employee::where('UserAccountID', auth()->user()->UserAccountID)
                ->where('IsDeleted', false)
                ->first();
                
            if (!$currentEmployee || $currentEmployee->Role !== 'Admin') {
                return redirect()->back()->with('error', 'Only administrators can update employees.');
            }

            DB::beginTransaction();

            $employee = Employee::findOrFail($employeeId);

            $request->validate([
                'FirstName' => 'required|string|max:255',
                'LastName' => 'required|string|max:255',
                'Address' => 'nullable|string',
                'Email' => 'required|email|max:255|unique:employee,Email,' . $employeeId . ',EmployeeID',
                'Gender' => 'required|string|in:Male,Female',
                'Role' => 'required|string|in:Admin,InventoryStaff,InventoryManager',
                'Username' => 'required|string|unique:useraccount,Username,' . $employee->UserAccountID . ',UserAccountID',
            ]);

            // Update employee with admin user as modifier
            $employee->update([
                'FirstName' => $request->FirstName,
                'LastName' => $request->LastName,
                'Address' => $request->Address,
                'Email' => $request->Email,
                'Gender' => $request->Gender,
                'Role' => $request->Role,
                'ModifiedByID' => $currentEmployee->EmployeeID,
                'DateModified' => now(),
            ]);

            if ($employee->userAccount) {
                $employee->userAccount->update([
                    'Username' => $request->Username,
                    'Role' => $request->Role,
                    'ModifiedByID' => $currentEmployee->EmployeeID,
                    'DateModified' => now(),
                ]);

                // Update password if provided
                if ($request->filled('Password')) {
                    $employee->userAccount->update([
                        'Password' => bcrypt($request->Password)
                    ]);
                }
            }

            DB::commit();

            \Log::info('Employee updated by admin:', [
                'admin_id' => $currentEmployee->EmployeeID,
                'employee_id' => $employee->EmployeeID,
                'name' => $employee->FirstName . ' ' . $employee->LastName
            ]);

            return redirect()->route('employees.index')
                ->with('success', 'Employee updated successfully by ' . $currentEmployee->FirstName . ' ' . $currentEmployee->LastName);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating employee:', [
                'admin_id' => $currentEmployee->EmployeeID ?? null,
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
        
        $employee = Employee::findOrFail($id);
        $employee->update([
            'IsDeleted' => false,
            'DeletedById' => null,
            'DateDeleted' => null,
            'RestoredById' => Auth::id(),
            'DateRestored' => now(),
            'ModifiedById' => null,
            'DateModified' => null
        ]);

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