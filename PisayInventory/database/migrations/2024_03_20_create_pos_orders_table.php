<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pos_orders', function (Blueprint $table) {
            $table->id('OrderID');
            $table->string('OrderNumber')->unique();
            $table->decimal('TotalAmount', 10, 2);
            $table->enum('PaymentMethod', ['cash', 'deposit']);
            $table->string('StudentID')->nullable();
            $table->enum('Status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('ProcessedBy')->nullable()->constrained('users', 'id');
            $table->timestamp('ProcessedAt')->nullable();
            $table->boolean('IsDeleted')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_orders');
    }
}; 