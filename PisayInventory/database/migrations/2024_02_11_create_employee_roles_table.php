<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('EmployeeId');
            $table->unsignedBigInteger('RoleId');
            $table->boolean('IsDeleted')->default(false);
            $table->dateTime('DateCreated')->nullable();
            $table->unsignedBigInteger('CreatedById')->nullable();
            $table->dateTime('DateModified')->nullable();
            $table->unsignedBigInteger('ModifiedById')->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->unsignedBigInteger('DeletedById')->nullable();
            $table->dateTime('DateRestored')->nullable();
            $table->unsignedBigInteger('RestoredById')->nullable();

            $table->foreign('EmployeeId')
                  ->references('EmployeeID')
                  ->on('employee')
                  ->onDelete('cascade');

            $table->foreign('RoleId')
                  ->references('RoleId')
                  ->on('roles')
                  ->onDelete('cascade');

            // Add composite unique key
            $table->unique(['EmployeeId', 'RoleId']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_roles');
    }
}; 