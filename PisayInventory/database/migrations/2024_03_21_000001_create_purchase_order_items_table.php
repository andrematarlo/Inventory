<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->bigIncrements('PurchaseOrderItemID');
            $table->unsignedBigInteger('PurchaseOrderID');
            $table->unsignedBigInteger('ItemId');
            $table->integer('Quantity');
            $table->decimal('UnitPrice', 10, 2);
            $table->decimal('TotalPrice', 10, 2);
            $table->unsignedBigInteger('CreatedByID')->nullable();
            $table->unsignedBigInteger('ModifiedByID')->nullable();
            $table->unsignedBigInteger('DeletedByID')->nullable();
            $table->unsignedBigInteger('RestoredById')->nullable();
            $table->datetime('DateCreated')->nullable();
            $table->datetime('DateModified')->nullable();
            $table->datetime('DateDeleted')->nullable();
            $table->datetime('DateRestored')->nullable();
            $table->boolean('IsDeleted')->default(false);

            // Add foreign key constraints
            $table->foreign('PurchaseOrderID')->references('PurchaseOrderID')->on('purchase_orders');
            $table->foreign('ItemId')->references('ItemId')->on('items');
            $table->foreign('CreatedByID')->references('id')->on('users');
            $table->foreign('ModifiedByID')->references('id')->on('users');
            $table->foreign('DeletedByID')->references('id')->on('users');
            $table->foreign('RestoredById')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_order_items');
    }
}; 