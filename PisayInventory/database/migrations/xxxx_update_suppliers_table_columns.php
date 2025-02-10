<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->renameColumn('SupplierName', 'CompanyName');
            $table->string('ContactPerson')->nullable()->after('CompanyName');
            $table->string('TelephoneNumber')->nullable()->after('ContactPerson');
        });
    }

    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->renameColumn('CompanyName', 'SupplierName');
            $table->dropColumn(['ContactPerson', 'TelephoneNumber']);
        });
    }
}; 