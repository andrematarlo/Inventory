<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateRolePoliciesStructure extends Migration
{
    public function up()
    {
        // First, drop existing policies
        DB::table('role_policies')->truncate();

        // Create Admin role policies
        $adminRole = DB::table('roles')->where('RoleName', 'Admin')->first();
        if ($adminRole) {
            $this->createAdminPolicies($adminRole->RoleId);
        }

        // Create Inventory Manager policies
        $inventoryManagerRole = DB::table('roles')->where('RoleName', 'InventoryManager')->first();
        if ($inventoryManagerRole) {
            $this->createInventoryManagerPolicies($inventoryManagerRole->RoleId);
        }

        // Create Inventory Staff policies
        $inventoryStaffRole = DB::table('roles')->where('RoleName', 'InventoryStaff')->first();
        if ($inventoryStaffRole) {
            $this->createInventoryStaffPolicies($inventoryStaffRole->RoleId);
        }

        // Create HR Manager policies
        $hrManagerRole = DB::table('roles')->where('RoleName', 'HRManager')->first();
        if ($hrManagerRole) {
            $this->createHRManagerPolicies($hrManagerRole->RoleId);
        }

        // Create HR Staff policies
        $hrStaffRole = DB::table('roles')->where('RoleName', 'HRStaff')->first();
        if ($hrStaffRole) {
            $this->createHRStaffPolicies($hrStaffRole->RoleId);
        }
    }

    private function createAdminPolicies($roleId)
    {
        $policies = [
            ['Module' => 'Roles', 'CanView' => true, 'CanAdd' => true, 'CanEdit' => true, 'CanDelete' => true],
            ['Module' => 'Suppliers', 'CanView' => true, 'CanAdd' => true, 'CanEdit' => true, 'CanDelete' => true],
            ['Module' => 'Users', 'CanView' => true, 'CanAdd' => true, 'CanEdit' => true, 'CanDelete' => true],
            ['Module' => 'Classifications', 'CanView' => true, 'CanAdd' => true, 'CanEdit' => true, 'CanDelete' => true],
            ['Module' => 'Inventory', 'CanView' => true, 'CanAdd' => true, 'CanEdit' => true, 'CanDelete' => true],
            ['Module' => 'Reports', 'CanView' => true, 'CanAdd' => true, 'CanEdit' => true, 'CanDelete' => true],
        ];

        $this->insertPolicies($roleId, $policies);
    }

    private function createInventoryManagerPolicies($roleId)
    {
        $policies = [
            ['Module' => 'Classifications', 'CanView' => true, 'CanAdd' => true, 'CanEdit' => true, 'CanDelete' => false],
            ['Module' => 'Inventory', 'CanView' => true, 'CanAdd' => true, 'CanEdit' => true, 'CanDelete' => false],
            ['Module' => 'Reports', 'CanView' => true, 'CanAdd' => true, 'CanEdit' => true, 'CanDelete' => false],
            ['Module' => 'Suppliers', 'CanView' => true, 'CanAdd' => true, 'CanEdit' => true, 'CanDelete' => false],
            ['Module' => 'Users', 'CanView' => true, 'CanAdd' => true, 'CanEdit' => true, 'CanDelete' => false],
        ];

        $this->insertPolicies($roleId, $policies);
    }

    private function createInventoryStaffPolicies($roleId)
    {
        $policies = [
            ['Module' => 'Classifications', 'CanView' => true, 'CanAdd' => false, 'CanEdit' => false, 'CanDelete' => false],
            ['Module' => 'Inventory', 'CanView' => true, 'CanAdd' => false, 'CanEdit' => false, 'CanDelete' => false],
            ['Module' => 'Reports', 'CanView' => true, 'CanAdd' => false, 'CanEdit' => false, 'CanDelete' => false],
            ['Module' => 'Suppliers', 'CanView' => true, 'CanAdd' => false, 'CanEdit' => false, 'CanDelete' => false],
        ];

        $this->insertPolicies($roleId, $policies);
    }

    private function createHRManagerPolicies($roleId)
    {
        $policies = [
            ['Module' => 'Employee', 'CanView' => true, 'CanAdd' => true, 'CanEdit' => true, 'CanDelete' => true],
        ];

        $this->insertPolicies($roleId, $policies);
    }

    private function createHRStaffPolicies($roleId)
    {
        $policies = [
            ['Module' => 'Employee', 'CanView' => true, 'CanAdd' => false, 'CanEdit' => false, 'CanDelete' => false],
        ];

        $this->insertPolicies($roleId, $policies);
    }

    private function insertPolicies($roleId, $policies)
    {
        foreach ($policies as $policy) {
            DB::table('role_policies')->insert([
                'RoleId' => $roleId,
                'Module' => $policy['Module'],
                'CanView' => $policy['CanView'],
                'CanAdd' => $policy['CanAdd'],
                'CanEdit' => $policy['CanEdit'],
                'CanDelete' => $policy['CanDelete'],
                'DateCreated' => now(),
                'CreatedById' => 1, // System user
                'IsDeleted' => false
            ]);
        }
    }

    public function down()
    {
        DB::table('role_policies')->truncate();
    }
}
