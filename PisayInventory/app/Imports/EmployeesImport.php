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

class EmployeesImport implements ToModel, WithHeadingRow, WithValidation
{
    private $columnMapping;
    private $createdById;
    private $existingRoles;

    public function __construct($columnMapping, $createdById)
    {
        $this->columnMapping = $columnMapping;
        $this->createdById = $createdById;
        // Get all existing roles for validation
        $this->existingRoles = Role::pluck('RoleName')->toArray();
    }

    public function model(array $row)
{
    try {
        Log::info('Processing row:', [
            'row_data' => $row,
            'column_mapping' => $this->columnMapping
        ]);

        DB::beginTransaction();

        // Convert keys to lowercase for case-insensitive matching
        $row = array_change_key_case($row, CASE_LOWER);
        
        // Map the Excel columns to our expected fields
        $mappedData = [
            'name' => trim($row['name'] ?? ''),
            'email' => trim($row['email'] ?? ''),
            'gender' => $this->normalizeGender(trim($row['gender'] ?? '')), // Normalize gender
            'role' => trim($row['role'] ?? ''),
            'address' => trim($row['address'] ?? '')
        ];

        // Validate all required fields are present
        foreach ($mappedData as $key => $value) {
            if (empty($value)) {
                throw new \Exception(ucfirst($key) . " is required");
            }
        }

        // Name splitting logic
        $nameParts = array_filter(explode(' ', $mappedData['name']));
        if (count($nameParts) < 2) {
            throw new \Exception("Full name must contain both first name and last name");
        }

        // Last word is the last name
        $lastName = array_pop($nameParts);
        // Everything else is the first name
        $firstName = implode(' ', $nameParts);

        Log::info('Name split result:', [
            'fullName' => $mappedData['name'],
            'firstName' => $firstName,
            'lastName' => $lastName
        ]);

        // Remove the strict gender validation since we've already normalized it
        // The normalized gender will always be either 'Male' or 'Female'


        // Find or create role
        $role = Role::firstOrCreate(
            ['RoleName' => $mappedData['role']],
            [
                'DateCreated' => now(),
                'CreatedById' => $this->createdById,
                'IsDeleted' => false
            ]
        );

        // Create username from email
        $username = explode('@', $mappedData['email'])[0];
        $username = preg_replace('/[^a-zA-Z0-9.]/', '', $username);
        $username = preg_replace('/\.+/', '.', $username);

        // Create UserAccount
        $userAccount = UserAccount::create([
            'Username' => $username,
            'Password' => Hash::make('default123'),
            'role' => $mappedData['role'],
            'IsDeleted' => false,
            'DateCreated' => now(),
            'CreatedById' => $this->createdById
        ]);

        // Create Employee
        $employee = new Employee([
            'UserAccountID' => $userAccount->UserAccountID,
            'FirstName' => $firstName,
            'LastName' => $lastName,
            'Email' => $mappedData['email'],
            'Address' => $mappedData['address'],
            'Gender' => $mappedData['gender'],
            'DateCreated' => now(),
            'CreatedByID' => $this->createdById,
            'ModifiedByID' => null,
            'DateModified' => null,
            'DeletedByID' => null,
            'RestoredById' => null,
            'DateDeleted' => null,
            'DateRestored' => null,
            'IsDeleted' => false
        ]);
        
        $employee->save();

        // Attach role in pivot table
        $employee->roles()->attach($role->RoleId, [
            'IsDeleted' => false,
            'DateCreated' => now(),
            'CreatedById' => $this->createdById
        ]);
            
        DB::commit();
        
        Log::info('Successfully imported employee:', [
            'email' => $mappedData['email'],
            'name' => $mappedData['name'],
            'role' => $mappedData['role']
        ]);

        return $employee;

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Row processing error:', [
            'error' => $e->getMessage(),
            'row' => $row,
            'mapped_data' => $mappedData ?? null
        ]);
        throw $e;
    }
}


// Add this new method to your class
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


public function rules(): array
{
    return [
        '*.name' => ['required', 'string'],
        '*.email' => ['required', 'email', 'unique:Employee,Email'],
        '*.gender' => ['required', 'in:Male,Female,M,F,male,female,m,f'], // Updated to be case-insensitive
        '*.role' => ['required', 'string'],
        '*.address' => ['required', 'string'],
    ];
}

    public function customValidationMessages()
    {
        return [
            $this->columnMapping['Email'].'.required' => 'Email is required',
            $this->columnMapping['Email'].'.email' => 'Email must be a valid email address',
            $this->columnMapping['Email'].'.unique' => 'Email already exists',
            $this->columnMapping['Name'].'.required' => 'Name is required',
            $this->columnMapping['Address'].'.required' => 'Address is required',
            $this->columnMapping['Gender'].'.required' => 'Gender is required',
            $this->columnMapping['Gender'].'.in' => 'Gender must be either Male or Female',
            $this->columnMapping['Role'].'.required' => 'Role is required',
        ];
    }
}