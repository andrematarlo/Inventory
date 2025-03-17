<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id('ItemID');
            $table->string('ItemName');
            $table->decimal('UnitPrice', 10, 2);
            $table->foreignId('ClassificationID')->constrained('classifications', 'ClassificationID');
            $table->string('ImagePath')->nullable();
            $table->boolean('IsDeleted')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
}; 