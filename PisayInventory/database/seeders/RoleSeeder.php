<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Example of adding a new role with default modules
        $roleId = DB::table('roles')->insertGetId(['RoleName' => 'NewRole', 'DateCreated' => now(), 'CreatedById' => 1]);

        // Copy default modules from Admin role
        $adminRoleId = DB::table('roles')->where('RoleName', 'Admin')->value('RoleId'); // Get Admin RoleId

        $defaultModules = DB::table('role_policies')
            ->where('RoleId', $adminRoleId) // Use the retrieved RoleId
            ->get();

        foreach ($defaultModules as $module) {
            DB::table('role_policies')->insert([
                'RoleId' => $roleId,
                'Module' => $module->Module,
                'CanView' => $module->CanView,
                'CanAdd' => $module->CanAdd,
                'CanEdit' => $module->CanEdit,
                'CanDelete' => $module->CanDelete,
                'DateCreated' => now(),
                'CreatedById' => 1,
                'IsDeleted' => $module->IsDeleted,
            ]);
        }
    }
} 