<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalColumnsToLaboratoryReservations extends Migration
{
    public function up()
    {
        Schema::table('laboratory_reservations', function (Blueprint $table) {
            // Add approved_at column if it doesn't exist
            if (!Schema::hasColumn('laboratory_reservations', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            
            // Add other approval-related columns if they don't exist
            if (!Schema::hasColumn('laboratory_reservations', 'approved_by')) {
                $table->string('approved_by')->nullable();
            }
            
            if (!Schema::hasColumn('laboratory_reservations', 'approver_role')) {
                $table->string('approver_role')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('laboratory_reservations', function (Blueprint $table) {
            $table->dropColumn(['approved_at', 'approved_by', 'approver_role']);
        });
    }
} 