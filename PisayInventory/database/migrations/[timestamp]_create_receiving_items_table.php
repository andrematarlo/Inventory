<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceivingItemsTable extends Migration
{
    public function up()
    {
        Schema::create('receiving_items', function (Blueprint $table) {
            $table->id('ReceivingItemID');
            $table->unsignedBigInteger('ReceivingID');
            $table->unsignedBigInteger('ItemId');
            $table->integer('Quantity');
            $table->decimal('UnitPrice', 10, 2);
            $table->boolean('IsDeleted')->default(false);
            $table->datetime('DateCreated');
            $table->unsignedBigInteger('CreatedByID')->nullable();
            $table->unsignedBigInteger('ModifiedByID')->nullable();
            $table->datetime('DateModified')->nullable();
            $table->unsignedBigInteger('DeletedByID')->nullable();
            $table->datetime('DateDeleted')->nullable();

            $table->foreign('ReceivingID')->references('ReceivingID')->on('receiving');
            $table->foreign('ItemId')->references('ItemId')->on('items');
            $table->foreign('CreatedByID')->references('EmployeeID')->on('employees');
            $table->foreign('ModifiedByID')->references('EmployeeID')->on('employees');
            $table->foreign('DeletedByID')->references('EmployeeID')->on('employees');
        });
    }

    public function down()
    {
        Schema::dropIfExists('receiving_items');
    }
} 