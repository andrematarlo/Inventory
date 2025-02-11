<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add receiving module for Admin role
        DB::table('role_policies')->insert([
            'RoleId' => 1, // Admin role ID
            'ModuleName' => 'receiving',
            'CanView' => true,
            'CanAdd' => true,
            'CanEdit' => true,
            'CanDelete' => true,
            'DateCreated' => now(),
            'IsDeleted' => false
        ]);

        // Add receiving module for Inventory Manager role
        DB::table('role_policies')->insert([
            'RoleId' => 2, // Inventory Manager role ID
            'ModuleName' => 'receiving',
            'CanView' => true,
            'CanAdd' => true,
            'CanEdit' => true,
            'CanDelete' => false,
            'DateCreated' => now(),
            'IsDeleted' => false
        ]);

        // Add receiving module for Inventory Staff role
        DB::table('role_policies')->insert([
            'RoleId' => 3, // Inventory Staff role ID
            'ModuleName' => 'receiving',
            'CanView' => true,
            'CanAdd' => true,
            'CanEdit' => false,
            'CanDelete' => false,
            'DateCreated' => now(),
            'IsDeleted' => false
        ]);
    }

    public function down()
    {
        DB::table('role_policies')
            ->where('ModuleName', 'receiving')
            ->delete();
    }
}; 