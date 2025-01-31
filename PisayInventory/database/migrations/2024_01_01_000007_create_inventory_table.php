<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Inventory', function (Blueprint $table) {
            $table->unsignedBigInteger('ItemId');
            $table->unsignedBigInteger('ClassificationId');
            $table->integer('StocksAvailable');
            $table->integer('StocksAdded');
            $table->dateTime('DateCreated')->nullable();
            $table->integer('CreatedById')->nullable();
            $table->dateTime('DateModified')->nullable();
            $table->integer('ModifiedById')->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->integer('DeletedById')->nullable();
            $table->boolean('IsDeleted')->default(false);
            
            $table->primary(['ItemId', 'ClassificationId']);
            
            $table->foreign('ItemId')
                  ->references('ItemId')
                  ->on('Items')
                  ->cascadeOnDelete();
            $table->foreign('ClassificationId')
                  ->references('ClassificationId')
                  ->on('Classification')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Inventory');
    }
}; 