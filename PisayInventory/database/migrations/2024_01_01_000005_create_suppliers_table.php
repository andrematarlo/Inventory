<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Suppliers', function (Blueprint $table) {
            $table->id('SupplierID');
            $table->string('SupplierName');
            $table->string('ContactNum', 20)->nullable();
            $table->text('Address')->nullable();
            $table->integer('CreatedById')->nullable();
            $table->dateTime('DateCreated')->nullable();
            $table->integer('ModifiedById')->nullable();
            $table->dateTime('DateModified')->nullable();
            $table->integer('DeletedById')->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->boolean('IsDeleted')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Suppliers');
    }
}; 