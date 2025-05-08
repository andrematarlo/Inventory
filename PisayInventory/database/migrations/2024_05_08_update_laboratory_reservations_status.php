<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('laboratory_reservations', function (Blueprint $table) {
            // Modify the status column to VARCHAR(50) to accommodate longer status values
            $table->string('status', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laboratory_reservations', function (Blueprint $table) {
            // Revert back to original length if needed
            $table->string('status', 20)->change();
        });
    }
}; 