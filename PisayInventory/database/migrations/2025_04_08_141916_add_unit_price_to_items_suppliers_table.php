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
        Schema::table('items_suppliers', function (Blueprint $table) {
            $table->decimal('UnitPrice', 10, 2)->nullable()->after('SupplierID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items_suppliers', function (Blueprint $table) {
            $table->dropColumn('UnitPrice');
        });
    }
};
