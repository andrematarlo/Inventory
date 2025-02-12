<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixInventoryTable extends Migration
{
    public function up()
    {
        Schema::table('inventory', function (Blueprint $table) {
            // Drop existing primary key if it exists
            $table->dropPrimary('InventoryId');
            
            // Modify InventoryId to be auto-incrementing
            $table->bigIncrements('InventoryId')->first()->change();
            
            // Make sure ClassificationId is not nullable
            $table->bigInteger('ClassificationId')->nullable(false)->change();
            
            // Set default values for nullable columns
            $table->dateTime('DateCreated')->nullable(false)->default(now())->change();
            $table->bigInteger('CreatedById')->nullable(false)->default(1)->change();
            $table->dateTime('DateModified')->nullable()->change();
            $table->bigInteger('ModifiedById')->nullable()->change();
            $table->dateTime('DateDeleted')->nullable()->change();
            $table->bigInteger('DeletedById')->nullable()->change();
            
            // Make sure StocksAdded and StocksAvailable have default values
            $table->integer('StocksAdded')->default(0)->change();
            $table->integer('StocksAvailable')->default(0)->change();
            
            // Make sure IsDeleted has a default value
            $table->boolean('IsDeleted')->default(false)->change();
        });
    }

    public function down()
    {
        Schema::table('inventory', function (Blueprint $table) {
            // Revert InventoryId to original state
            $table->bigInteger('InventoryId')->change();
            
            // Allow ClassificationId to be nullable
            $table->bigInteger('ClassificationId')->nullable()->change();
            
            // Remove default values
            $table->dateTime('DateCreated')->nullable()->change();
            $table->bigInteger('CreatedById')->nullable()->change();
            $table->integer('StocksAdded')->default(null)->change();
            $table->integer('StocksAvailable')->default(null)->change();
            $table->boolean('IsDeleted')->default(null)->change();
        });
    }
}
