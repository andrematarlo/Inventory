<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('RoleId');
            $table->string('ModuleName');
            $table->boolean('CanView')->default(false);
            $table->boolean('CanAdd')->default(false);
            $table->boolean('CanEdit')->default(false);
            $table->boolean('CanDelete')->default(false);
            $table->dateTime('DateCreated')->nullable();
            $table->unsignedBigInteger('CreatedById')->nullable();
            $table->dateTime('DateModified')->nullable();
            $table->unsignedBigInteger('ModifiedById')->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->unsignedBigInteger('DeletedById')->nullable();
            $table->dateTime('DateRestored')->nullable();
            $table->unsignedBigInteger('RestoredById')->nullable();
            $table->boolean('IsDeleted')->default(false);
            
            $table->foreign('RoleId')->references('RoleId')->on('roles');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_policies');
    }
}; 