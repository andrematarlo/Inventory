<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // Drop existing foreign key if it exists
            $table->dropForeign(['ClassificationId']);
            
            // Make sure the column exists and is properly typed
            $table->unsignedBigInteger('ClassificationId')->change();
            
            // Add the foreign key constraint with the correct reference
            $table->foreign('ClassificationId')
                  ->references('ClassificationId')
                  ->on('classification')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['ClassificationId']);
        });
    }
}; 