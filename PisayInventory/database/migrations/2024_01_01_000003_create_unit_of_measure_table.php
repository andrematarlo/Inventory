<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('UnitOfMeasure', function (Blueprint $table) {
            $table->id('UnitOfMeasureId');
            $table->string('UnitName');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('UnitOfMeasure');
    }
}; 