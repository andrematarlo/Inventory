<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->bigIncrements('PurchaseOrderID');
            $table->string('PONumber');
            $table->unsignedBigInteger('SupplierID');
            $table->datetime('OrderDate');
            $table->string('Status');
            $table->decimal('TotalAmount', 10, 2);
            $table->datetime('DateCreated');
            $table->unsignedBigInteger('CreatedByID')->nullable();
            $table->unsignedBigInteger('ModifiedByID')->nullable();
            $table->datetime('DateModified')->nullable();
            $table->unsignedBigInteger('DeletedByID')->nullable();
            $table->unsignedBigInteger('RestoredById')->nullable();
            $table->datetime('DateDeleted')->nullable();
            $table->datetime('DateRestored')->nullable();
            $table->boolean('IsDeleted')->default(false);

            $table->foreign('SupplierID')
                  ->references('SupplierID')
                  ->on('suppliers')
                  ->onDelete('restrict');

            $table->foreign('CreatedByID')
                  ->references('EmployeeID')
                  ->on('employee')
                  ->onDelete('restrict');

            $table->foreign('ModifiedByID')
                  ->references('EmployeeID')
                  ->on('employee')
                  ->onDelete('restrict');

            $table->foreign('DeletedByID')
                  ->references('EmployeeID')
                  ->on('employee')
                  ->onDelete('restrict');

            $table->foreign('RestoredById')
                  ->references('EmployeeID')
                  ->on('employee')
                  ->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_order');
    }
}; 