<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['ClassificationID']);
            
            // Rename the column
            $table->renameColumn('ClassificationID', 'ClassificationId');
            
            // Add the foreign key constraint back with the new column name
            $table->foreign('ClassificationId')
                  ->references('ClassificationId')
                  ->on('classification')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['ClassificationId']);
            
            // Rename the column back
            $table->renameColumn('ClassificationId', 'ClassificationID');
            
            // Add the foreign key constraint back with the old column name
            $table->foreign('ClassificationID')
                  ->references('ClassificationId')
                  ->on('classification')
                  ->onDelete('set null');
        });
    }
}; 