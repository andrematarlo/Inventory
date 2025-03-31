<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModifyPosOrdersStatusEnum extends Migration
{
    public function up()
    {
        // First, modify the enum to include all our new statuses
        DB::statement("ALTER TABLE pos_orders MODIFY COLUMN Status ENUM('pending', 'paid', 'preparing', 'ready', 'cancelled') DEFAULT 'pending'");
    }

    public function down()
    {
        // Revert back to the original enum values
        DB::statement("ALTER TABLE pos_orders MODIFY COLUMN Status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending'");
    }
} 