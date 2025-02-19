<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemSupplierTable extends Migration
{
    public function up()
    {
        Schema::create('item_supplier', function (Blueprint $table) {
            $table->bigInteger('ItemId');
            $table->bigInteger('SupplierID');
            $table->primary(['ItemId', 'SupplierID']);
            $table->foreign('ItemId')->references('ItemId')->on('items')->onDelete('cascade');
            $table->foreign('SupplierID')->references('SupplierID')->on('suppliers')->onDelete('cascade');
            $table->timestamp('DateCreated')->useCurrent();
            $table->bigInteger('CreatedById')->nullable();
            $table->foreign('CreatedById')->references('UserAccountID')->on('UserAccount');
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_supplier');
    }
} 