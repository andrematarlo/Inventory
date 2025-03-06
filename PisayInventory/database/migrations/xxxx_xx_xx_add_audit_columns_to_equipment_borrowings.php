<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('equipment_borrowings', function (Blueprint $table) {
            if (!Schema::hasColumn('equipment_borrowings', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('UserAccountID')->on('user_accounts');
            }
            
            if (!Schema::hasColumn('equipment_borrowings', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('UserAccountID')->on('user_accounts');
            }
            
            if (!Schema::hasColumn('equipment_borrowings', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->foreign('deleted_by')->references('UserAccountID')->on('user_accounts');
            }
            
            if (!Schema::hasColumn('equipment_borrowings', 'restored_by')) {
                $table->unsignedBigInteger('restored_by')->nullable();
                $table->foreign('restored_by')->references('UserAccountID')->on('user_accounts');
            }
            
            if (!Schema::hasColumn('equipment_borrowings', 'restored_at')) {
                $table->timestamp('restored_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('equipment_borrowings', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);
            $table->dropForeign(['restored_by']);
            
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
            $table->dropColumn('deleted_by');
            $table->dropColumn('restored_by');
            $table->dropColumn('restored_at');
        });
    }
}; 