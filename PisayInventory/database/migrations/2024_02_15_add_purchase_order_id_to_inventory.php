<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseOrderIdToInventory extends Migration
{
    public function up()
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->unsignedBigInteger('PurchaseOrderID')->nullable()->after('ItemId');
            $table->foreign('PurchaseOrderID')
                  ->references('PurchaseOrderID')
                  ->on('purchase_order')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropForeign(['PurchaseOrderID']);
            $table->dropColumn('PurchaseOrderID');
        });
    }
} 