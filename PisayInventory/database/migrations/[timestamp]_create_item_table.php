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
        Schema::create('Item', function (Blueprint $table) {
            $table->id('ItemID');
            $table->string('ItemName');
            $table->string('Description')->nullable();
            $table->integer('CurrentStock');
            $table->integer('MinimumStock');
            $table->integer('CreatedById')->nullable();
            $table->dateTime('DateCreated')->nullable();
            $table->integer('ModifiedById')->nullable();
            $table->dateTime('DateModified')->nullable();
            $table->integer('DeletedById')->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->boolean('IsDeleted')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Item');
    }
}; 