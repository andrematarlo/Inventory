<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('UserAccount', function (Blueprint $table) {
            $table->id('UserAccountId');
            $table->string('Username')->unique();
            $table->string('Password');
            $table->string('FirstName');
            $table->string('LastName');
            $table->string('Email')->unique();
            $table->string('ContactNum')->nullable();
            $table->string('remember_token')->nullable();
            $table->integer('CreatedById')->nullable();
            $table->dateTime('DateCreated')->nullable();
            $table->integer('ModifiedById')->nullable();
            $table->dateTime('DateModified')->nullable();
            $table->integer('DeletedById')->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->boolean('IsDeleted')->default(false);
        });
    }

    public function down()
    {
        Schema::dropIfExists('UserAccount');
    }
}; 