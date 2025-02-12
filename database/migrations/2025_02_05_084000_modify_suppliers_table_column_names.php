<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySuppliersTableColumnNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Rename columns to match your existing model
            $table->renameColumn('SupplierId', 'SupplierID');
            $table->renameColumn('ContactNumber', 'ContactNum');
            
            // Drop Email column if not used
            $table->dropColumn('Email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Reverse the column renames
            $table->renameColumn('SupplierID', 'SupplierId');
            $table->renameColumn('ContactNum', 'ContactNumber');
            
            // Add back Email column
            $table->string('Email')->nullable();
        });
    }
}
