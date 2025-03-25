<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_type');
            $table->decimal('amount_tendered', 10, 2)->nullable();
            $table->decimal('change_amount', 10, 2)->nullable();
            $table->string('status');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('Students');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('menu_item_id');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('cascade');
            $table->foreign('menu_item_id')
                  ->references('MenuItemID')
                  ->on('menu_items');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
}; 