<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['deposit', 'purchase']);
            $table->string('notes')->nullable();
            $table->decimal('balance_after', 10, 2);
            $table->string('order_number')->nullable();
            $table->timestamps();

            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('students')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deposits');
    }
}; 