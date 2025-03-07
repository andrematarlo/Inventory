<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->string('equipment_id', 50)->primary();
            $table->string('equipment_name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->string('laboratory_id', 50)->nullable();
            $table->string('serial_number', 50)->nullable();
            $table->string('model_number', 50)->nullable();
            $table->string('condition', 50)->nullable();
            $table->string('status', 20)->nullable()->default('Available');
            $table->date('acquisition_date')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedBigInteger('RestoredById')->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
            $table->datetime('deleted_at')->nullable();
            $table->datetime('DateRestored')->nullable();
            $table->boolean('IsDeleted')->nullable()->default(0);

            $table->foreign('laboratory_id')->references('laboratory_id')->on('laboratories');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
            $table->foreign('RestoredById')->references('UserAccountID')->on('useraccount');
        });
    }

    public function down()
    {
        Schema::dropIfExists('equipment');
    }
}; 