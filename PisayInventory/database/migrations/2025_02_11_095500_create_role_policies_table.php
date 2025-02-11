<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRolePoliciesTable extends Migration
{
    public function up()
    {
        Schema::create('role_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('RoleId');
            $table->string('ModuleName');
            $table->boolean('CanView')->default(false);
            $table->boolean('CanAdd')->default(false);
            $table->boolean('CanEdit')->default(false);
            $table->boolean('CanDelete')->default(false);
            $table->dateTime('DateCreated')->nullable();
            $table->unsignedBigInteger('CreatedById')->nullable();
            $table->dateTime('DateModified')->nullable();
            $table->unsignedBigInteger('ModifiedById')->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->dateTime('DateRestored')->nullable();
            $table->unsignedBigInteger('DeletedById')->nullable();
            $table->unsignedBigInteger('RestoredById')->nullable();
            $table->boolean('IsDeleted')->default(false);
            
            $table->foreign('RoleId')->references('RoleId')->on('roles')->onDelete('cascade');
            $table->foreign('CreatedById')->references('UserAccountID')->on('UserAccount')->onDelete('set null');
            $table->foreign('ModifiedById')->references('UserAccountID')->on('UserAccount')->onDelete('set null');
            $table->foreign('DeletedById')->references('UserAccountID')->on('UserAccount')->onDelete('set null');
            $table->foreign('RestoredById')->references('UserAccountID')->on('UserAccount')->onDelete('set null');
            $table->unique(['RoleId', 'ModuleName']);
        });

        // Insert default policies
        DB::table('role_policies')->insert([
            // Admin Permissions (Full Access)
            [
                'RoleId' => 1,
                'ModuleName' => 'Classifications',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => true,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],
            [
                'RoleId' => 1,
                'ModuleName' => 'Inventory',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => true,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],
            [
                'RoleId' => 1,
                'ModuleName' => 'Reports',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => true,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],
            [
                'RoleId' => 1,
                'ModuleName' => 'Roles',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => true,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],
            [
                'RoleId' => 1,
                'ModuleName' => 'Suppliers',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => true,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],
            [
                'RoleId' => 1,
                'ModuleName' => 'Users',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => true,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],
            [
                'RoleId' => 1,
                'ModuleName' => 'POS',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => true,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],

            // Inventory Staff Permissions
            [
                'RoleId' => 2,
                'ModuleName' => 'Classifications',
                'CanView' => true,
                'CanAdd' => false,
                'CanEdit' => false,
                'CanDelete' => false,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],
            [
                'RoleId' => 2,
                'ModuleName' => 'Inventory',
                'CanView' => true,
                'CanAdd' => false,
                'CanEdit' => false,
                'CanDelete' => false,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],
            [
                'RoleId' => 2,
                'ModuleName' => 'Reports',
                'CanView' => true,
                'CanAdd' => false,
                'CanEdit' => false,
                'CanDelete' => false,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],

            // Inventory Manager Permissions
            [
                'RoleId' => 3,
                'ModuleName' => 'Classifications',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => false,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],
            [
                'RoleId' => 3,
                'ModuleName' => 'Inventory',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => false,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],
            [
                'RoleId' => 3,
                'ModuleName' => 'Reports',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => false,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],
            [
                'RoleId' => 3,
                'ModuleName' => 'Suppliers',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => false,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],

            // Cashier Permissions
            [
                'RoleId' => 7,
                'ModuleName' => 'POS',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => false,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],
            [
                'RoleId' => 7,
                'ModuleName' => 'Inventory',
                'CanView' => true,
                'CanAdd' => false,
                'CanEdit' => false,
                'CanDelete' => false,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],

            // HR Manager Permissions
            [
                'RoleId' => 12,
                'ModuleName' => 'Employee',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => true,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],
            [
                'RoleId' => 12,
                'ModuleName' => 'Reports',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => false,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],

            // HR Staff Permissions
            [
                'RoleId' => 13,
                'ModuleName' => 'Employee',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => false,
                'DateCreated' => now(),
                'IsDeleted' => false
            ],
            [
                'RoleId' => 13,
                'ModuleName' => 'Reports',
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => false,
                'CanDelete' => false,
                'DateCreated' => now(),
                'IsDeleted' => false
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('role_policies');
    }
}
