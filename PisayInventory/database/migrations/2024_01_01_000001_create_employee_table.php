<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Employee', function (Blueprint $table) {
            $table->id('EmployeeID');
            $table->string('EmpFirstName');
            $table->string('EmpLastName');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Employee');
    }
}; 