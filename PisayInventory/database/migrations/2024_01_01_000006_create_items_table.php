<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Items', function (Blueprint $table) {
            $table->id('ItemId');
            $table->string('ItemName');
            $table->text('Description')->nullable();
            $table->unsignedBigInteger('UnitOfMeasureId')->nullable();
            $table->unsignedBigInteger('ClassificationId')->nullable();
            $table->unsignedBigInteger('SupplierID')->nullable();
            $table->integer('CreatedById')->nullable();
            $table->dateTime('DateCreated')->nullable();
            $table->integer('ModifiedById')->nullable();
            $table->dateTime('DateModified')->nullable();
            $table->integer('DeletedById')->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->boolean('IsDeleted')->default(false);
            
            $table->foreign('UnitOfMeasureId')
                  ->references('UnitOfMeasureId')
                  ->on('UnitOfMeasure')
                  ->nullOnDelete();
            $table->foreign('ClassificationId')
                  ->references('ClassificationId')
                  ->on('Classification')
                  ->nullOnDelete();
            $table->foreign('SupplierID')
                  ->references('SupplierID')
                  ->on('Suppliers')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Items');
    }
}; 