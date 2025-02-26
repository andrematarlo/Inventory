<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id('PurchaseId');
            $table->unsignedBigInteger('ItemId');
            $table->unsignedBigInteger('SupplierId');
            $table->integer('Quantity');
            $table->decimal('UnitPrice', 10, 2);
            $table->decimal('TotalAmount', 10, 2);
            $table->string('PurchaseOrderNumber')->nullable();
            $table->date('PurchaseDate');
            $table->text('Notes')->nullable();
            $table->unsignedBigInteger('CreatedById');
            $table->timestamp('DateCreated');
            $table->unsignedBigInteger('ModifiedById')->nullable();
            $table->timestamp('DateModified')->nullable();
            $table->unsignedBigInteger('DeletedById')->nullable();
            $table->timestamp('DateDeleted')->nullable();
            $table->unsignedBigInteger('RestoredById')->nullable();
            $table->timestamp('DateRestored')->nullable();
            $table->boolean('IsDeleted')->default(false);

            $table->foreign('ItemId')->references('ItemId')->on('items');
            $table->foreign('SupplierId')->references('SupplierID')->on('suppliers');
            $table->foreign('CreatedById')->references('UserAccountID')->on('UserAccount');
            $table->foreign('ModifiedById')->references('UserAccountID')->on('UserAccount');
            $table->foreign('DeletedById')->references('UserAccountID')->on('UserAccount');
            $table->foreign('RestoredById')->references('UserAccountID')->on('UserAccount');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}; 