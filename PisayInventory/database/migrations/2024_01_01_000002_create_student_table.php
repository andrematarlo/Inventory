<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Student', function (Blueprint $table) {
            $table->id('StudentID');
            $table->string('StudFirstName');
            $table->string('StudLastName');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Student');
    }
}; 