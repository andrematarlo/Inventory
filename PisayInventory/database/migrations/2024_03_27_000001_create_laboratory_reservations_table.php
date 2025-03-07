<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('laboratory_reservations', function (Blueprint $table) {
            $table->string('reservation_id', 50)->primary();
            $table->string('laboratory_id', 50);
            $table->integer('reserver_id');
            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('purpose')->nullable();
            $table->integer('num_students')->nullable();
            $table->string('status', 20)->default('Active');
            $table->text('remarks')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedBigInteger('RestoredById')->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
            $table->datetime('deleted_at')->nullable();
            $table->datetime('DateRestored')->nullable();
            $table->boolean('IsDeleted')->default(false);

            $table->foreign('laboratory_id')->references('laboratory_id')->on('laboratories');
            $table->foreign('reserver_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('laboratory_reservations');
    }
}; 