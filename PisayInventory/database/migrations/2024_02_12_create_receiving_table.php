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
            $table->string('Status');
            $table->text('Notes')->nullable();
            $table->dateTime('DateCreated');
            $table->unsignedBigInteger('CreatedById');
            $table->dateTime('DateModified')->nullable();
            $table->unsignedBigInteger('ModifiedById')->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->unsignedBigInteger('DeletedById')->nullable();
            $table->dateTime('DateRestored')->nullable();
            $table->unsignedBigInteger('RestoredById')->nullable();
            $table->boolean('IsDeleted')->default(false);

            $table->foreign('PurchaseOrderID')->references('PurchaseOrderID')->on('purchase_orders');
            $table->foreign('ReceivedByID')->references('EmployeeID')->on('employee');
            $table->foreign('CreatedById')->references('EmployeeID')->on('employee');
            $table->foreign('ModifiedById')->references('EmployeeID')->on('employee');
            $table->foreign('DeletedById')->references('EmployeeID')->on('employee');
            $table->foreign('RestoredById')->references('EmployeeID')->on('employee');
        });
    }

    public function down()
    {
        Schema::dropIfExists('receiving');
    }
}; 