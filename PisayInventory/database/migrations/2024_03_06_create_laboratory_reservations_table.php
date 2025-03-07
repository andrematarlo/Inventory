<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('laboratory_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('laboratory_id');
            $table->foreign('laboratory_id')->references('laboratory_id')->on('laboratories');
            $table->foreignId('user_id')->constrained();
            $table->text('purpose');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->dateTime('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('laboratory_reservations');
    }
}; 