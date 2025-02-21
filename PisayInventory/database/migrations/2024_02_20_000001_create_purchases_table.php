<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->bigInteger('PurchaseOrderID')->autoIncrement();
            $table->string('PONumber', 255)->nullable();
            $table->bigInteger('SupplierID')->unsigned();
            $table->dateTime('OrderDate');
            $table->string('Status', 255);
            $table->decimal('TotalAmount', 10, 2);
            $table->dateTime('DateCreated');
            $table->bigInteger('CreatedByID')->unsigned()->nullable();
            $table->bigInteger('ModifiedByID')->unsigned()->nullable();
            $table->dateTime('DateModified')->nullable();
            $table->bigInteger('DeletedByID')->unsigned()->nullable();
            $table->bigInteger('RestoredByID')->unsigned()->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->dateTime('DateRestored')->nullable();
            $table->tinyInteger('IsDeleted')->default(0);

            // Foreign keys
            $table->foreign('SupplierID')
                  ->references('SupplierID')
                  ->on('suppliers')
                  ->onDelete('restrict');

            $table->foreign('CreatedByID')
                  ->references('EmployeeID')
                  ->on('employees')
                  ->onDelete('restrict');

            $table->foreign('ModifiedByID')
                  ->references('EmployeeID')
                  ->on('employees')
                  ->onDelete('restrict');

            $table->foreign('DeletedByID')
                  ->references('EmployeeID')
                  ->on('employees')
                  ->onDelete('restrict');

            $table->foreign('RestoredByID')
                  ->references('EmployeeID')
                  ->on('employees')
                  ->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_order');
    }
}; 