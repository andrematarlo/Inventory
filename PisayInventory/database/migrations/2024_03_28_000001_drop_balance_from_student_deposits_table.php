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
        Schema::table('student_deposits', function (Blueprint $table) {
            $table->dropColumn('Balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_deposits', function (Blueprint $table) {
            $table->decimal('Balance', 10, 2)->nullable();
        });
    }
}; 