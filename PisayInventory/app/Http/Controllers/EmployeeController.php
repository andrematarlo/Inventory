<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\UserAccount;
use App\Models\RolePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Imports\EmployeesImport;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmployeeController extends Controller
{
    public function getUserPermissions($module = null)
    {
        return parent::getUserPermissions('Employee Management');
    }

    public function index()
    {
        try {
            $activeEmployees = Employee::with(['createdBy', 'roles'])
                ->where('IsDeleted', false)
                ->orderBy('LastName')
                ->get();

            $deletedEmployees = Employee::with(['createdBy', 'roles'])
                ->where('IsDeleted', true)
                ->orderBy('LastName')
                ->get();

            // Get roles for the import modal
            $roles = Role::where('IsDeleted', false)->get();

            // Add default permissions
            $userPermissions = (object)[
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => true
            ];

            return view('employees.index', [
                'activeEmployees' => $activeEmployees,
                'deletedEmployees' => $deletedEmployees,
                'roles' => $roles,
                'userPermissions' => $userPermissions
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading employees: ' . $e->getMessage());
            return back()->with('error', 'Error loading employees: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
                'column_mapping' => 'required|array'
            ]);

            DB::beginTransaction();

            try {
                // Get the current authenticated employee
                $currentEmployee = Employee::where('UserAccountID', Auth::id())
                    ->where('IsDeleted', false)
                    ->first();

                if (!$currentEmployee) {
                    throw new \Exception('Current employee record not found');
                }

                Log::info('Import initiated by:', [
                    'employee_id' => $currentEmployee->EmployeeID,
                    'name' => $currentEmployee->FirstName . ' ' . $currentEmployee->LastName
                ]);

                $import = new EmployeesImport(
                    $request->column_mapping,
                    $currentEmployee->EmployeeID
                );

                Excel::import($import, $request->file('file'));

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Employees imported successfully.'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error importing employees:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error importing employees: ' . $e->getMessage()
            ], 500);
        }
    }

    public function previewColumns(Request $request)
    {
        try {
            $request->validate([
                'excel_file' => 'required|mimes:xlsx,xls',
            ]);

            $path = $request->file('excel_file')->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $worksheet = $spreadsheet->getActiveSheet();
            $columns = [];

            // Get the first row (headers)
            foreach ($worksheet->getRowIterator(1, 1) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                foreach ($cellIterator as $cell) {
                    $colName = trim($cell->getValue());
                    if (!empty($colName)) {
                        // Clean the column name
                        $colName = str_replace(['_', '-'], ' ', $colName);
                        $colName = trim($colName);
                        $columns[] = $colName;
                    }
                }
            }

            // Map similar column names
            $columnMappings = [
                'Name' => ['name', 'full name', 'fullname', 'complete name'],
                'Email' => ['email', 'e-mail', 'mail', 'email address'],
                'Address' => ['address', 'addr', 'location'],
                'Gender' => ['gender', 'sex'],
                'Role' => ['role', 'roles', 'position', 'designation']
            ];

            Log::info('Excel columns found:', [
                'columns' => $columns,
                'mappings' => $columnMappings
            ]);

            return response()->json([
                'columns' => $columns,
                'mappings' => $columnMappings
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error previewing Excel columns: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            $request->validate([
                'format' => 'required|in:xlsx,csv',
                'fields' => 'required|array|min:1',
                'fields.*' => 'required|in:FirstName,LastName,Email,Gender,Role,Address',
                'employees_status' => 'required|in:active,deleted,all'
            ]);

            $export = new EmployeesExport($request->fields, $request->employees_status);
            $filename = 'employees_' . date('Y-m-d_His') . '.' . $request->format;

            return Excel::download($export, $filename);
        } catch (\Exception $e) {
            Log::error('Error exporting employees: ' . $e->getMessage());
            return back()->with('error', 'Error exporting employees: ' . $e->getMessage());
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

            if (!str_contains($userAccount->role, 'Admin')) {
                return redirect()->back()->with('error', 'Only administrators can add employees.');
            }

            // Log current employee info
            \Log::info('Current employee creating new employee:', [
                'creator_id' => $currentEmployee->EmployeeID,
                'creator_name' => $currentEmployee->FirstName . ' ' . $currentEmployee->LastName,
                'creator_role' => $currentEmployee->Role
            ]);

            $request->validate([
                'FirstName' => 'required|string|max:100',
                'LastName' => 'required|string|max:100',
                'Email' => [
                    'required',
                    'email',
                    'max:100',
                    'unique:employee,Email',
                    'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
                    'ends_with:gmail.com'
                ],
                'Gender' => 'required|in:Male,Female',
                'Address' => 'required|string',
                'Username' => 'required|string|unique:useraccount,Username',
                'Password' => 'required|string|min:6',
                'roles' => 'required|array|min:1',
                'roles.*' => 'required|exists:roles,RoleId'
            ], [
                'Email.regex' => 'The email must be a valid Gmail address (@gmail.com)',
                'Email.ends_with' => 'The email must end with @gmail.com'
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

                // Verify we have an EmployeeID before proceeding
                if (!$employee->EmployeeID) {
                    throw new \Exception('Failed to get EmployeeID after creation');
                }

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

    public function edit($id)
    {
        try {
            // Debug logging
            Log::info('Raw employee edit data:', [
                'received_id' => $id,
                'id_type' => gettype($id)
            ]);

            // Extract ID from object if needed
            if (is_object($id) || is_array($id)) {
                if (isset($id->EmployeeID)) {
                    $id = $id->EmployeeID;
                } elseif (is_array($id) && isset($id['EmployeeID'])) {
                    $id = $id['EmployeeID'];
                } elseif (property_exists($id, 'App\Models\Employee')) {
                    $employeeData = $id->{'App\Models\Employee'};
                    $id = $employeeData->EmployeeID;
                }
            }

            // If it's a JSON string, try to decode it
            if (is_string($id) && strpos($id, '{') !== false) {
                $decoded = json_decode($id, true);
                if (isset($decoded['EmployeeID'])) {
                    $id = $decoded['EmployeeID'];
                }
            }

            // Final validation
            if (!is_numeric($id)) {
                throw new \Exception('Invalid employee ID');
            }

            $employee = Employee::with('roles')
                ->where('EmployeeID', $id)
                ->where('IsDeleted', false)
                ->first();

            if (!$employee) {
                throw new \Exception('Employee not found');
            }

            $roles = Role::where('IsDeleted', false)
                ->orderBy('RoleName')
                ->get();

            return view('employees.edit', compact('employee', 'roles'));
        } catch (\Exception $e) {
            Log::error('Error loading edit employee form:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error loading form: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Debug logging
            Log::info('Raw employee update data:', [
                'received_id' => $id,
                'id_type' => gettype($id)
            ]);

            // Extract ID from object if needed
            if (is_object($id) || is_array($id)) {
                if (isset($id->EmployeeID)) {
                    $id = $id->EmployeeID;
                } elseif (is_array($id) && isset($id['EmployeeID'])) {
                    $id = $id['EmployeeID'];
                } elseif (property_exists($id, 'App\Models\Employee')) {
                    $employeeData = $id->{'App\Models\Employee'};
                    $id = $employeeData->EmployeeID;
                }
            }

            // If it's a JSON string, try to decode it
            if (is_string($id) && strpos($id, '{') !== false) {
                $decoded = json_decode($id, true);
                if (isset($decoded['EmployeeID'])) {
                    $id = $decoded['EmployeeID'];
                }
            }

            // Final validation
            if (!is_numeric($id)) {
                throw new \Exception('Invalid employee ID');
            }

            // Find the employee
            $employee = Employee::findOrFail($id);
            
            // Validate basic fields
            $validationRules = [
                'FirstName' => 'required|string|max:100',
                'LastName' => 'required|string|max:100',
                'Email' => [
                    'required',
                    'email',
                    'max:100',
                    'unique:employee,Email,' . $id . ',EmployeeID',
                    'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
                    'ends_with:gmail.com'
                ],
                'Username' => 'required|string|max:100|unique:UserAccount,Username,' . $employee->userAccount->UserAccountID . ',UserAccountID',
                'Gender' => 'required|in:Male,Female',
                'Address' => 'required|string|max:65535',
                'roles' => 'required|array|min:1',
                'roles.*' => 'exists:roles,RoleId'
            ];

            $messages = [
                'Email.regex' => 'The email must be a valid Gmail address (@gmail.com)',
                'Email.ends_with' => 'The email must end with @gmail.com'
            ];

            // Add password validation only if password is being updated
            if ($request->filled('Password')) {
                $validationRules['Password'] = 'required|string|min:6|confirmed';
            }

            $request->validate($validationRules, $messages);

            // Start transaction
            DB::beginTransaction();
            
            try {
                // Update employee details
                $employee->FirstName = $request->FirstName;
                $employee->LastName = $request->LastName;
                $employee->Email = $request->Email;
                $employee->Gender = $request->Gender;
                $employee->Address = $request->Address;
                $employee->save();

                // Update user account
                if ($employee->userAccount) {
                    $userAccount = UserAccount::find($employee->userAccount->UserAccountID);
                    if ($userAccount) {
                        $userAccount->Username = $request->Username;
                        
                        // Update password only if provided
                        if ($request->filled('Password')) {
                            $userAccount->Password = Hash::make($request->Password);
                        }

                        // Update the role field to match the first selected role's name
                        if ($request->has('roles') && count($request->roles) > 0) {
                            $primaryRoleId = $request->roles[0];
                            $primaryRole = \App\Models\Role::find($primaryRoleId);
                            if ($primaryRole) {
                                $userAccount->role = $primaryRole->RoleName;
                            }
                        }
                        $userAccount->save();
                    }
                }

                // Sync roles - this will add new roles and remove unchecked ones
                if ($request->has('roles')) {
                    // Remove all current roles
                    DB::table('employee_roles')->where('EmployeeId', $employee->EmployeeID)->delete();
                    // Insert new roles with DateCreated and CreatedById
                    $now = now();
                    $createdById = Auth::id();
                    foreach ($request->roles as $roleId) {
                        DB::table('employee_roles')->insert([
                            'EmployeeId' => $employee->EmployeeID,
                            'RoleId' => $roleId,
                            'IsDeleted' => false,
                            'DateCreated' => $now,
                            'CreatedById' => $createdById
                        ]);
                    }
                } else {
                    // If no roles were selected, remove all roles
                    $employee->roles()->detach();
                }

                DB::commit();
                
                return redirect()->route('employees.index')
                    ->with('success', 'Employee updated successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Error updating employee: ' . $e->getMessage())
                    ->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Error updating employee:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error updating employee: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Debug logging
            Log::info('Raw employee delete data:', [
                'received_id' => $id,
                'id_type' => gettype($id)
            ]);

            // Check permissions first
            $userPermissions = $this->getUserPermissions();
            if (!$userPermissions || !$userPermissions->CanDelete) {
                throw new \Exception('You do not have permission to delete employees.');
            }

            // Extract ID from object if needed
            if (is_object($id) || is_array($id)) {
                if (isset($id->EmployeeID)) {
                    $id = $id->EmployeeID;
                } elseif (is_array($id) && isset($id['EmployeeID'])) {
                    $id = $id['EmployeeID'];
                } elseif (property_exists($id, 'App\Models\Employee')) {
                    $employeeData = $id->{'App\Models\Employee'};
                    $id = $employeeData->EmployeeID;
                }
            }

            // If it's a JSON string, try to decode it
            if (is_string($id) && strpos($id, '{') !== false) {
                $decoded = json_decode($id, true);
                if (isset($decoded['EmployeeID'])) {
                    $id = $decoded['EmployeeID'];
                }
            }

            // Final validation
            if (!is_numeric($id)) {
                throw new \Exception('Invalid employee ID');
            }

            $employee = Employee::where('EmployeeID', $id)->first();
            if (!$employee) {
                throw new \Exception('Employee not found');
            }

            $employee->update([
                'IsDeleted' => true,
                'DeletedById' => Auth::id(),
                'DateDeleted' => now()
            ]);

            DB::commit();
            return redirect()->route('employees.index')
                ->with('success', 'Employee moved to trash successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee deletion failed:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error deleting employee: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            DB::beginTransaction();

            $employee = Employee::where('EmployeeID', $id)
                ->where('IsDeleted', true)
                ->firstOrFail();

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            // Update employee record
            $employee->update([
                'IsDeleted' => false,
                'RestoredById' => $currentEmployee->EmployeeID,
                'DateRestored' => now(),
                'DeletedByID' => null,
                'DateDeleted' => null,
                'ModifiedByID' => $currentEmployee->EmployeeID,
                'DateModified' => now()
            ]);

            // Also restore associated user account if exists
            if ($employee->userAccount) {
                $employee->userAccount->update([
                    'IsDeleted' => false,
                    'RestoredById' => $currentEmployee->EmployeeID,
                    'DateRestored' => now(),
                    'DeletedByID' => null,
                    'DateDeleted' => null,
                    'ModifiedByID' => $currentEmployee->EmployeeID,
                    'DateModified' => now()
                ]);
            }

            DB::commit();
            return redirect()->route('employees.index')
                ->with('success', 'Employee restored successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restoring employee: ' . $e->getMessage());
            return redirect()->route('employees.index')
                ->with('error', 'Failed to restore employee: ' . $e->getMessage());
        }
    }
} 