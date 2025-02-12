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
        // First, modify the RoleId column to be auto-increment
        DB::statement('ALTER TABLE roles MODIFY RoleId bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove auto-increment from RoleId
        DB::statement('ALTER TABLE roles MODIFY RoleId bigint(20) UNSIGNED NOT NULL');
    }
};
