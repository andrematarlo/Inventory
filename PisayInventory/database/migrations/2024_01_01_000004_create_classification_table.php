<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Classification', function (Blueprint $table) {
            $table->id('ClassificationId');
            $table->string('ClassificationName');
            $table->unsignedBigInteger('ParentClassificationId')->nullable();
            $table->foreign('ParentClassificationId')
                  ->references('ClassificationId')
                  ->on('Classification')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Classification');
    }
}; 