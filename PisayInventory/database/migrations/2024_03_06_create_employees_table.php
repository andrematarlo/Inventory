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
        Schema::create('employee', function (Blueprint $table) {
            $table->id('EmployeeID');
            $table->unsignedBigInteger('UserAccountID');
            $table->string('FirstName', 100);
            $table->string('LastName', 100);
            $table->string('Email', 100)->unique();
            $table->enum('Gender', ['Male', 'Female']);
            $table->text('Address');
            $table->datetime('DateCreated');
            $table->unsignedBigInteger('CreatedByID')->nullable();
            $table->unsignedBigInteger('ModifiedByID')->nullable();
            $table->datetime('DateModified')->nullable();
            $table->unsignedBigInteger('DeletedByID')->nullable();
            $table->unsignedBigInteger('RestoredById')->nullable();
            $table->datetime('DateDeleted')->nullable();
            $table->datetime('DateRestored')->nullable();
            $table->boolean('IsDeleted')->default(false);

            // Foreign keys
            $table->foreign('UserAccountID')->references('UserAccountID')->on('user_accounts');
            $table->foreign('CreatedByID')->references('UserAccountID')->on('user_accounts');
            $table->foreign('ModifiedByID')->references('UserAccountID')->on('user_accounts');
            $table->foreign('DeletedByID')->references('UserAccountID')->on('user_accounts');
            $table->foreign('RestoredById')->references('UserAccountID')->on('user_accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee');
    }
}; 