<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('receiving', function (Blueprint $table) {
            $table->id('ReceivingID');
            $table->unsignedBigInteger('PurchaseOrderID');
            $table->unsignedBigInteger('ReceivedByID');
            $table->dateTime('DateReceived');
            $table->string('Status', 255);
            $table->text('Notes')->nullable();
            $table->longText('ItemStatuses')->nullable();
            $table->dateTime('DateCreated');
            $table->unsignedBigInteger('CreatedByID')->nullable();
            $table->unsignedBigInteger('ModifiedByID')->nullable();
            $table->dateTime('DateModified')->nullable();
            $table->unsignedBigInteger('DeletedByID')->nullable();
            $table->unsignedBigInteger('RestoredById')->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->dateTime('DateRestored')->nullable();
            $table->boolean('IsDeleted')->default(false);

            $table->foreign('PurchaseOrderID')->references('PurchaseOrderID')->on('purchase_order');
            $table->foreign('ReceivedByID')->references('EmployeeID')->on('employees');
            $table->foreign('CreatedByID')->references('EmployeeID')->on('employees');
            $table->foreign('ModifiedByID')->references('EmployeeID')->on('employees');
            $table->foreign('DeletedByID')->references('EmployeeID')->on('employees');
            $table->foreign('RestoredById')->references('EmployeeID')->on('employees');
        });
    }

    public function down()
    {
        Schema::dropIfExists('receiving');
    }
}; 