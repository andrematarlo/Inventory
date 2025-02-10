<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clear existing policies
        DB::table('role_policies')->truncate();

        // Admin Role Policies
        $this->createPolicies(1, [
            'Roles' => ['view' => 1, 'add' => 1, 'edit' => 1, 'delete' => 1],
            'Suppliers' => ['view' => 1, 'add' => 1, 'edit' => 1, 'delete' => 1],
            'Users' => ['view' => 1, 'add' => 1, 'edit' => 1, 'delete' => 1],
        ]);

        // Inventory Manager Policies
        $this->createPolicies(2, [
            'Classifications' => ['view' => 1, 'add' => 1, 'edit' => 1, 'delete' => 0],
            'Inventory' => ['view' => 1, 'add' => 1, 'edit' => 1, 'delete' => 0],
            'Reports' => ['view' => 1, 'add' => 1, 'edit' => 1, 'delete' => 0],
            'Suppliers' => ['view' => 1, 'add' => 1, 'edit' => 1, 'delete' => 0],
            'Users' => ['view' => 1, 'add' => 1, 'edit' => 1, 'delete' => 0],
        ]);

        // Inventory Staff Policies
        $this->createPolicies(3, [
            'Classifications' => ['view' => 1, 'add' => 0, 'edit' => 0, 'delete' => 0],
            'Inventory' => ['view' => 1, 'add' => 0, 'edit' => 0, 'delete' => 0],
            'Reports' => ['view' => 1, 'add' => 0, 'edit' => 0, 'delete' => 0],
            'Suppliers' => ['view' => 1, 'add' => 0, 'edit' => 0, 'delete' => 0],
        ]);
    }

    /**
     * Create policies for a role
     */
    private function createPolicies($roleId, $modules)
    {
        foreach ($modules as $module => $permissions) {
            DB::table('role_policies')->insert([
                'RoleId' => $roleId,
                'Module' => $module,
                'CanView' => $permissions['view'],
                'CanAdd' => $permissions['add'],
                'CanEdit' => $permissions['edit'],
                'CanDelete' => $permissions['delete'],
                'DateCreated' => now(),
                'CreatedById' => 1, // Assuming 1 is the system admin ID
                'IsDeleted' => 0
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('role_policies')->truncate();
    }
};
