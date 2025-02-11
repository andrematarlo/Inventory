<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, add the useraccount columns to employee table
        Schema::table('employee', function (Blueprint $table) {
            $table->string('Username')->unique()->after('EmployeeID');
            $table->string('Password')->after('Username');
        });

        // Copy data from useraccount to employee
        DB::statement("
            UPDATE employee e
            INNER JOIN useraccount u ON e.UserAccountID = u.UserAccountID
            SET e.Username = u.Username,
                e.Password = u.Password
            WHERE e.IsDeleted = 0
        ");

        // Drop the useraccount table
        Schema::dropIfExists('useraccount');

        // Remove the UserAccountID column from employee
        Schema::table('employee', function (Blueprint $table) {
            $table->dropColumn('UserAccountID');
        });
    }

    public function down()
    {
        // Recreate useraccount table
        Schema::create('useraccount', function (Blueprint $table) {
            $table->id('UserAccountID');
            $table->string('Username')->unique();
            $table->string('Password');
            $table->string('role');
            $table->dateTime('DateCreated')->nullable();
            $table->unsignedBigInteger('CreatedByID')->nullable();
            $table->dateTime('DateModified')->nullable();
            $table->unsignedBigInteger('ModifiedByID')->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->unsignedBigInteger('DeletedByID')->nullable();
            $table->dateTime('DateRestored')->nullable();
            $table->unsignedBigInteger('RestoredByID')->nullable();
            $table->boolean('IsDeleted')->default(false);
        });

        // Add UserAccountID back to employee
        Schema::table('employee', function (Blueprint $table) {
            $table->unsignedBigInteger('UserAccountID')->after('EmployeeID')->nullable();
            $table->dropColumn(['Username', 'Password']);
        });
    }
}; 