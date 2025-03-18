<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('items_suppliers', function (Blueprint $table) {
            $table->boolean('IsDeleted')->default(false);
            $table->unsignedBigInteger('DeletedById')->nullable();
            $table->dateTime('DateDeleted')->nullable();
            $table->unsignedBigInteger('ModifiedById')->nullable();
            $table->dateTime('DateModified')->nullable();
            $table->unsignedBigInteger('RestoredById')->nullable();
            $table->dateTime('DateRestored')->nullable();

            $table->foreign('DeletedById')->references('UserAccountID')->on('UserAccount');
            $table->foreign('ModifiedById')->references('UserAccountID')->on('UserAccount');
            $table->foreign('RestoredById')->references('UserAccountID')->on('UserAccount');
        });
    }

    public function down()
    {
        Schema::table('items_suppliers', function (Blueprint $table) {
            $table->dropForeign(['DeletedById']);
            $table->dropForeign(['ModifiedById']);
            $table->dropForeign(['RestoredById']);
            
            $table->dropColumn([
                'IsDeleted',
                'DeletedById',
                'DateDeleted',
                'ModifiedById',
                'DateModified',
                'RestoredById',
                'DateRestored'
            ]);
        });
    }
}; 