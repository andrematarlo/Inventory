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
            ->leftJoin('useraccount as ua', 'employee.UserAccountID', '=', 'ua.UserAccountID')
            ->leftJoin('employee as cb', function($join) {
                $join->on('employee.CreatedByID', '=', 'cb.EmployeeID');
            })
            ->leftJoin('employee as mb', function($join) {
                $join->on('employee.ModifiedByID', '=', 'mb.EmployeeID');
            })
            ->where('employee.IsDeleted', 0)
            ->orderBy('employee.LastName')
            ->get();

            // Debug log to check the data
            \Log::info('Employee Data:', [
                'employees' => $employees->map(function($emp) {
                    return [
                        'id' => $emp->EmployeeID,
                        'name' => $emp->FirstName . ' ' . $emp->LastName,
                        'created_by_id' => $emp->CreatedByID,
                        'created_by_name' => $emp->CreatedByFirstName . ' ' . $emp->CreatedByLastName,
                        'modified_by_id' => $emp->ModifiedByID,
                        'modified_by_name' => $emp->ModifiedByFirstName . ' ' . $emp->ModifiedByLastName,
                    ];
                })
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
                'roles.*' => 'required|string|in:Admin,InventoryStaff,InventoryManager'
            ]);

            DB::beginTransaction();

            try {
                // Create user account first
                $newUserAccount = UserAccount::create([
                    'Username' => $request->Username,
                    'Password' => Hash::make($request->Password),
                    'CreatedByID' => $currentEmployee->EmployeeID,
                    'DateCreated' => now(),
                    'IsDeleted' => false
                ]);

                // Log user account creation
                \Log::info('User account created:', [
                    'user_account_id' => $newUserAccount->UserAccountID,
                    'created_by_id' => $currentEmployee->EmployeeID
                ]);

                // Get the next EmployeeID
                $lastEmployee = Employee::orderBy('EmployeeID', 'desc')->first();
                $nextEmployeeID = $lastEmployee ? $lastEmployee->EmployeeID + 1 : 1;

                // Create employee data array for better debugging
                $employeeData = [
                    'EmployeeID' => $nextEmployeeID,
                    'FirstName' => $request->FirstName,
                    'LastName' => $request->LastName,
                    'Email' => $request->Email,
                    'Gender' => $request->Gender,
                    'Address' => $request->Address,
                    'UserAccountID' => $newUserAccount->UserAccountID,
                    'Role' => implode(', ', $request->roles),
                    'CreatedByID' => $currentEmployee->EmployeeID,
                    'DateCreated' => now(),
                    'ModifiedByID' => $currentEmployee->EmployeeID,
                    'DateModified' => now(),
                    'IsDeleted' => false
                ];

                // Log employee data before creation
                \Log::info('Creating employee with data:', $employeeData);

                // Create employee
                $employee = Employee::create($employeeData);

                // Log created employee
                \Log::info('Employee created:', [
                    'employee_id' => $employee->EmployeeID,
                    'created_by_id' => $employee->CreatedByID,
                ]);

                DB::commit();

                try {
                    // Verification step - if this fails, we still want to show success
                    $verifyEmployee = Employee::with('createdBy')->find($employee->EmployeeID);
                    if ($verifyEmployee) {
                        \Log::info('Verifying created employee:', [
                            'employee_id' => $verifyEmployee->EmployeeID,
                            'created_by_id' => $verifyEmployee->CreatedByID,
                            'created_by_info' => $verifyEmployee->createdBy ? [
                                'id' => $verifyEmployee->createdBy->EmployeeID,
                                'name' => $verifyEmployee->createdBy->FirstName . ' ' . $verifyEmployee->createdBy->LastName
                            ] : null
                        ]);
                    }
                } catch (\Exception $e) {
                    // Log verification error but don't throw it
                    \Log::warning('Verification step failed but employee was created:', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }

                return redirect()->route('employees.index')
                    ->with('success', 'Employee created successfully');

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
            
            // Check if current user is admin
            $currentEmployee = Employee::where('UserAccountID', auth()->user()->UserAccountID)
                ->where('IsDeleted', false)
                ->first();
                
            if (!$currentEmployee || $currentEmployee->Role !== 'Admin') {
                return redirect()->back()->with('error', 'Only administrators can edit employees.');
            }

            $request->validate([
                'FirstName' => 'required|string|max:255',
                'LastName' => 'required|string|max:255',
                'Email' => 'required|email|max:255',
                'Gender' => 'required|in:Male,Female',
                'Address' => 'required|string',
                'Username' => 'required|string|unique:useraccount,Username,' . $employee->UserAccountID . ',UserAccountID',
                'roles' => 'required|array|min:1',
                'roles.*' => 'required|string|in:Admin,InventoryStaff,InventoryManager'
            ]);

            DB::beginTransaction();

            // Update employee
            $employee->update([
                'FirstName' => $request->FirstName,
                'LastName' => $request->LastName,
                'Email' => $request->Email,
                'Gender' => $request->Gender,
                'Address' => $request->Address,
                'Role' => implode(', ', $request->roles),
                'ModifiedByID' => $currentEmployee->EmployeeID,
                'DateModified' => now()
            ]);

            // Update user account if username changed
            if ($employee->userAccount->Username !== $request->Username) {
                $employee->userAccount->update([
                    'Username' => $request->Username,
                    'ModifiedByID' => $currentEmployee->EmployeeID,
                    'DateModified' => now()
                ]);
            }

            DB::commit();

            return redirect()->route('employees.index')
                ->with('success', 'Employee updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating employee:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
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