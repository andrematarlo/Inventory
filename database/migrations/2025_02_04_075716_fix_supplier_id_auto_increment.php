<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the primary key if it exists
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropPrimary();
        });

        // Modify the SupplierID column to be auto-incrementing
        Schema::table('suppliers', function (Blueprint $table) {
            $table->increments('SupplierID')->first();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropPrimary();
            $table->integer('SupplierID')->first()->change();
        });
    }
};
