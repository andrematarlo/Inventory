<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('POS', function (Blueprint $table) {
            $table->id('PurchaseId');
            $table->unsignedBigInteger('ItemId')->nullable();
            $table->unsignedBigInteger('UnitOfMeasureId')->nullable();
            $table->unsignedBigInteger('ClassificationId')->nullable();
            $table->integer('Quantity');
            $table->integer('StocksAdded');
            $table->dateTime('DateCreated')->nullable();
            $table->integer('CreatedById')->nullable();
            $table->dateTime('DateModified')->nullable();
            $table->integer('ModifiedById')->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->integer('DeletedById')->nullable();
            $table->boolean('IsDeleted')->default(false);
            
            $table->foreign('ItemId')
                  ->references('ItemId')
                  ->on('Items')
                  ->nullOnDelete();
            $table->foreign('UnitOfMeasureId')
                  ->references('UnitOfMeasureId')
                  ->on('UnitOfMeasure')
                  ->nullOnDelete();
            $table->foreign('ClassificationId')
                  ->references('ClassificationId')
                  ->on('Classification')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('POS');
    }
}; 