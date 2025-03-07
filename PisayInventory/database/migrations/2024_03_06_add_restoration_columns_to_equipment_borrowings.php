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
        Schema::table('equipment_borrowing', function (Blueprint $table) {
            if (!Schema::hasColumn('equipment_borrowing', 'RestoredById')) {
                $table->bigint('RestoredById')->nullable();
                $table->foreign('RestoredById')
                    ->references('UserAccountID')
                    ->on('useraccount')
                    ->onDelete('set null');
            }

            if (!Schema::hasColumn('equipment_borrowing', 'DateRestored')) {
                $table->datetime('DateRestored')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_borrowing', function (Blueprint $table) {
            $table->dropForeign(['RestoredById']);
            $table->dropColumn(['RestoredById', 'DateRestored']);
        });
    }
}; 