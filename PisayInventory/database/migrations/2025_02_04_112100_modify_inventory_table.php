<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('inventory');
        
        Schema::create('inventory', function (Blueprint $table) {
            $table->bigIncrements('InventoryId');
            $table->unsignedBigInteger('ItemId');
            $table->unsignedBigInteger('ClassificationId');
            $table->integer('StocksAdded')->default(0);
            $table->integer('StocksAvailable')->default(0);
            $table->dateTime('DateCreated')->useCurrent();
            $table->unsignedBigInteger('CreatedById');
            $table->dateTime('DateModified')->nullable();
            $table->unsignedBigInteger('ModifiedById')->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->unsignedBigInteger('DeletedById')->nullable();
            $table->boolean('IsDeleted')->default(false);

            $table->foreign('ItemId')->references('ItemId')->on('items');
            $table->foreign('ClassificationId')->references('ClassificationId')->on('classification');
            $table->foreign('CreatedById')->references('UserAccountID')->on('UserAccount');
            $table->foreign('ModifiedById')->references('UserAccountID')->on('UserAccount');
            $table->foreign('DeletedById')->references('UserAccountID')->on('UserAccount');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory');
    }
};
