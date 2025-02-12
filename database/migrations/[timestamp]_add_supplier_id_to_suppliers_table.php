<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierIdToSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->bigInteger('SupplierId')->unsigned()->autoIncrement()->first();
            $table->string('SupplierName');
            $table->string('Address')->nullable();
            $table->string('ContactNumber')->nullable();
            $table->string('Email')->nullable();
            $table->bigInteger('CreatedById')->unsigned()->nullable();
            $table->dateTime('DateCreated')->nullable();
            $table->bigInteger('ModifiedById')->unsigned()->nullable();
            $table->dateTime('DateModified')->nullable();
            $table->bigInteger('DeletedById')->unsigned()->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->boolean('IsDeleted')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'SupplierId',
                'SupplierName',
                'Address',
                'ContactNumber',
                'Email',
                'CreatedById',
                'DateCreated',
                'ModifiedById',
                'DateModified',
                'DeletedById',
                'DateDeleted',
                'IsDeleted'
            ]);
        });
    }
} 