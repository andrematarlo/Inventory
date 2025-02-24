<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('modules', function (Blueprint $table) {
            // Add Description field if it doesn't exist
            if (!Schema::hasColumn('modules', 'Description')) {
                $table->string('Description')->nullable();
            }
            
            // Add soft delete fields
            $table->boolean('IsDeleted')->default(false)->after('ModuleName');
            $table->bigInteger('DeletedById')->unsigned()->nullable()->after('IsDeleted');
            $table->timestamp('DateDeleted')->nullable()->after('DeletedById');
            $table->bigInteger('RestoredById')->unsigned()->nullable()->after('DateDeleted');
            $table->timestamp('DateRestored')->nullable()->after('RestoredById');
            
            // Add foreign key constraints
            $table->foreign('DeletedById')
                  ->references('EmployeeID')
                  ->on('employee')
                  ->onDelete('restrict');
                  
            $table->foreign('RestoredById')
                  ->references('EmployeeID')
                  ->on('employee')
                  ->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['DeletedById']);
            $table->dropForeign(['RestoredById']);
            
            // Drop columns
            $table->dropColumn([
                'Description',
                'IsDeleted',
                'DeletedById',
                'DateDeleted',
                'RestoredById',
                'DateRestored'
            ]);
        });
    }
}; 