<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->bigInteger('StudentID')->autoIncrement();
            $table->string('StudentNumber', 50)->unique();
            $table->string('FirstName', 255);
            $table->string('LastName', 255);
            $table->string('MiddleName', 255)->nullable();
            $table->string('Email', 255)->nullable();
            $table->string('Gender', 10);
            $table->string('Address', 500)->nullable();
            $table->string('ContactNumber', 50)->nullable();
            $table->string('ParentName', 255)->nullable();
            $table->string('ParentContact', 50)->nullable();
            $table->string('YearLevel', 50)->nullable();
            $table->string('Section', 50)->nullable();
            $table->dateTime('DateCreated');
            $table->bigInteger('CreatedByID')->unsigned()->nullable();
            $table->bigInteger('ModifiedByID')->unsigned()->nullable();
            $table->dateTime('DateModified')->nullable();
            $table->bigInteger('DeletedByID')->unsigned()->nullable();
            $table->bigInteger('RestoredByID')->unsigned()->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->dateTime('DateRestored')->nullable();
            $table->tinyInteger('IsDeleted')->default(0);

            // Foreign keys
            $table->foreign('CreatedByID')
                  ->references('EmployeeID')
                  ->on('employee')
                  ->onDelete('restrict');

            $table->foreign('ModifiedByID')
                  ->references('EmployeeID')
                  ->on('employee')
                  ->onDelete('restrict');

            $table->foreign('DeletedByID')
                  ->references('EmployeeID')
                  ->on('employee')
                  ->onDelete('restrict');

            $table->foreign('RestoredByID')
                  ->references('EmployeeID')
                  ->on('employee')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
}; 