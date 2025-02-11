<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminRolePolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Admin role ID
        $adminRoleId = DB::table('roles')
            ->where('RoleName', 'Admin')
            ->value('RoleId');

        if (!$adminRoleId) {
            // Create Admin role if it doesn't exist
            $adminRoleId = DB::table('roles')->insertGetId([
                'RoleName' => 'Admin',
                'Description' => 'Administrator with full system access',
                'IsDeleted' => false
            ]);
        }

        // Delete existing policies for Admin
        DB::table('role_policies')
            ->where('RoleId', $adminRoleId)
            ->delete();

        // List of all modules
        $modules = [
            'Inventory',
            'Items',
            'Suppliers',
            'Units',
            'Classifications',
            'Reports',
            'Purchases',
            'Roles',
            'Users',
            'Employees'
        ];

        // Create full access permissions for each module
        foreach ($modules as $module) {
            DB::table('role_policies')->insert([
                'RoleId' => $adminRoleId,
                'Module' => $module,
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => true,
                'IsDeleted' => false
            ]);
        }
    }
}
