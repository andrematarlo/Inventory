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
            'Laboratory Reservations' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => false],
            'Equipment Borrowings' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => false],
            'Students' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => false],
            'Reports' => ['view' => true, 'add' => false, 'edit' => false, 'delete' => false]
        ];

        // Assign permissions for each module
        foreach ($modules as $module => $permissions) {
            DB::table('role_policies')->insert([
                'RoleId' => $roleId,
                'Module' => $module,
                'CanView' => $permissions['view'],
                'CanAdd' => $permissions['add'],
                'CanEdit' => $permissions['edit'],
                'CanDelete' => $permissions['delete'],
                'DateCreated' => now(),
                'CreatedById' => 1, // Admin user ID (adjust if needed)
                'IsDeleted' => false
            ]);
        }
    }
} 