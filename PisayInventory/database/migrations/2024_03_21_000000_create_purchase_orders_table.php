<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id('PurchaseOrderID');
            $table->string('PONumber')->unique();
            $table->foreignId('SupplierID')->constrained('suppliers');
            $table->date('OrderDate');
            $table->enum('Status', ['Pending', 'Approved', 'Rejected', 'Completed'])->default('Pending');
            $table->decimal('TotalAmount', 10, 2)->default(0);
            $table->foreignId('CreatedByID')->nullable()->constrained('users');
            $table->foreignId('ModifiedByID')->nullable()->constrained('users');
            $table->foreignId('DeletedByID')->nullable()->constrained('users');
            $table->foreignId('RestoredById')->nullable()->constrained('users');
            $table->timestamp('DateCreated')->nullable();
            $table->timestamp('DateModified')->nullable();
            $table->timestamp('DateDeleted')->nullable();
            $table->timestamp('DateRestored')->nullable();
            $table->boolean('IsDeleted')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_orders');
    }
}; 