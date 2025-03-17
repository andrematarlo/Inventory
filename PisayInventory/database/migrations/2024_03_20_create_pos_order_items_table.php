<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pos_order_items', function (Blueprint $table) {
            $table->id('OrderItemID');
            $table->foreignId('OrderID')->constrained('pos_orders', 'OrderID');
            $table->foreignId('ItemID')->constrained('items', 'ItemID');
            $table->integer('Quantity');
            $table->decimal('UnitPrice', 10, 2);
            $table->decimal('Subtotal', 10, 2);
            $table->boolean('IsDeleted')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_order_items');
    }
}; 