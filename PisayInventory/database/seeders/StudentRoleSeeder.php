<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentRoleSeeder extends Seeder
{
    public function run()
    {
        // Add the Student role
        $roleId = DB::table('roles')->insertGetId([
            'RoleName' => 'Student',
            'Description' => 'Role for students with permissions to access student-specific features',
            'DateCreated' => now(),
            'CreatedById' => 1, // Admin user ID (adjust if needed)
            'IsDeleted' => false
        ]);

        // Define the modules that students can access
        $modules = [
            'Laboratory Reservations' => [
                'CanView' => true, 
                'CanAdd' => true, 
                'CanEdit' => true, 
                'CanDelete' => false
            ]
        ];

        // Assign permissions for each module
        foreach ($modules as $moduleName => $permissions) {
            DB::table('role_policies')->insert([
                'RoleId' => $roleId,
                'ModuleName' => $moduleName,
                'CanView' => $permissions['CanView'],
                'CanAdd' => $permissions['CanAdd'],
                'CanEdit' => $permissions['CanEdit'],
                'CanDelete' => $permissions['CanDelete'],
                'DateCreated' => now(),
                'CreatedById' => 1, // Admin user ID (adjust if needed)
                'IsDeleted' => false
            ]);
        }
    }
} 