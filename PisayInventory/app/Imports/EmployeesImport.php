<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Role;
use App\Models\UserAccount;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeesImport implements ToModel, WithHeadingRow, WithValidation
{
    private $columnMapping;
    private $createdById;
    private $existingRoles;
    private $createdByEmployee;

    public function __construct($columnMapping, $createdById)
    {
        // Convert column names to lowercase and remove spaces for consistent mapping
        $this->columnMapping = array_map(function($column) {
            return Str::slug($column, '');
        }, $columnMapping);
        
        $this->createdById = $createdById;
        
        // Get the creator's employee record
        $this->createdByEmployee = Employee::find($createdById);
        if (!$this->createdByEmployee) {
            throw new \Exception('Creator employee not found');
        }
        
        $this->existingRoles = Role::pluck('RoleName')->toArray();
    }

    public function model(array $row)
    {
        try {
            // Convert row keys to the same format as the mapping
            $processedRow = [];
            foreach ($row as $key => $value) {
                $processedKey = Str::slug($key, '');
                $processedRow[$processedKey] = $value;
            }

            // Get values from Excel using the processed column mapping
            $firstName = $processedRow[$this->columnMapping['FirstName']] ?? null;
            $lastName = $processedRow[$this->columnMapping['LastName']] ?? null;
            $email = $processedRow[$this->columnMapping['Email']] ?? null;
            $gender = $processedRow[$this->columnMapping['Gender']] ?? null;
            $role = $processedRow[$this->columnMapping['Role']] ?? null;
            $address = $processedRow[$this->columnMapping['Address']] ?? null;

            // Validate required fields
            if (!$firstName || !$lastName || !$email || !$role) {
                throw new \Exception("Missing required fields");
            }

            // Normalize gender and role
            $gender = $this->normalizeGender($gender);
            $role = $this->normalizeRole($role);

            // Create user account
            $userAccount = UserAccount::create([
                'Username' => strtolower($firstName . '.' . $lastName),
                'Password' => Hash::make('password123'),
                'role' => $role,
                'CreatedById' => $this->createdById,
                'DateCreated' => now(),
                'ModifiedById' => $this->createdById,
                'DateModified' => now(),
                'IsDeleted' => false
            ]);

            // Create employee record
            $employee = new Employee([
                'UserAccountID' => $userAccount->UserAccountID,
                'FirstName' => $firstName,
                'LastName' => $lastName,
                'Email' => $email,
                'Gender' => $gender,
                'Address' => $address,
                'DateCreated' => now(),
                'CreatedByID' => $this->createdById,
                'ModifiedByID' => $this->createdById,
                'DateModified' => now(),
                'IsDeleted' => false
            ]);

            // Log the values before saving
            Log::info('Creating Employee:', [
                'creator_id' => $this->createdById,
                'employee_data' => $employee->toArray(),
                'user_account_data' => $userAccount->toArray()
            ]);

            // Save the employee
            $employee->save();

            // Verify the saved data
            Log::info('Employee Created:', [
                'employee_id' => $employee->EmployeeID,
                'created_by_id' => $employee->CreatedByID,
                'creator' => $employee->createdBy
            ]);

            // Now attach the role
            if ($role) {
                $roleModel = Role::where('RoleName', $role)
                               ->where('IsDeleted', false)
                               ->first();

                if ($roleModel) {
                    DB::table('employee_roles')->insert([
                        'EmployeeId' => $employee->EmployeeID,
                        'RoleId' => $roleModel->RoleId,
                        'IsDeleted' => false,
                        'DateCreated' => now(),
                        'CreatedByID' => $this->createdById
                    ]);
                }
            }

            return $employee;

        } catch (\Exception $e) {
            Log::error('Error importing row:', [
                'error' => $e->getMessage(),
                'row' => $row,
                'processed_row' => $processedRow ?? [],
                'column_mapping' => $this->columnMapping,
                'creator_id' => $this->createdById,
                'creator_employee' => $this->createdByEmployee ? [
                    'id' => $this->createdByEmployee->EmployeeID,
                    'name' => $this->createdByEmployee->FirstName . ' ' . $this->createdByEmployee->LastName
                ] : null
            ]);
            throw $e;
        }
    }

    private function normalizeGender($gender)
    {
        $gender = strtoupper(trim($gender));
        return match($gender) {
            'M' => 'Male',
            'F' => 'Female',
            'MALE' => 'Male',
            'FEMALE' => 'Female',
            default => throw new \Exception("Gender must be either 'Male', 'Female', 'M', or 'F'")
        };
    }

    private function normalizeRole($role)
    {
        if (empty($role)) {
            return null;
        }

        // Get all active roles
        $existingRoles = Role::where('IsDeleted', false)->get();

        // Try to find an exact match first
        $exactMatch = $existingRoles->first(function($existingRole) use ($role) {
            return strtolower($existingRole->RoleName) === strtolower($role);
        });

        if ($exactMatch) {
            return $exactMatch->RoleName;
        }

        // If no exact match, try to find a partial match
        $partialMatch = $existingRoles->first(function($existingRole) use ($role) {
            return str_contains(strtolower($existingRole->RoleName), strtolower($role)) ||
                   str_contains(strtolower($role), strtolower($existingRole->RoleName));
        });

        if ($partialMatch) {
            return $partialMatch->RoleName;
        }

        throw new \Exception("Invalid role: '$role'. Available roles are: " . $existingRoles->pluck('RoleName')->implode(', '));
    }

    public function rules(): array
    {
        $mappedColumns = [];
        foreach ($this->columnMapping as $field => $column) {
            $processedColumn = Str::slug($column, '');
            $mappedColumns[$processedColumn] = ['required', 'string'];
            
            // Add specific validations
            if ($field === 'Email') {
                $mappedColumns[$processedColumn][] = 'email';
                $mappedColumns[$processedColumn][] = 'unique:Employee,Email';
            }
            elseif ($field === 'Gender') {
                $mappedColumns[$processedColumn] = ['required', 'in:Male,Female,M,F,male,female,m,f'];
            }
            elseif ($field === 'Role') {
                // Add role validation
                $mappedColumns[$processedColumn] = ['required', 'string'];
            }
        }

        return $mappedColumns;
    }

    public function customValidationMessages()
    {
        $messages = [];
        foreach ($this->columnMapping as $field => $column) {
            $processedColumn = Str::slug($column, '');
            $messages[$processedColumn.'.required'] = $field . ' is required';
            
            if ($field === 'Email') {
                $messages[$processedColumn.'.email'] = 'Email must be a valid email address';
                $messages[$processedColumn.'.unique'] = 'Email already exists';
            }
            elseif ($field === 'Gender') {
                $messages[$processedColumn.'.in'] = 'Gender must be either Male or Female';
            }
            elseif ($field === 'Role') {
                $messages[$processedColumn.'.required'] = 'Role is required';
            }
        }
        return $messages;
    }
}