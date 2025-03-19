<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id('MenuItemID');
            $table->string('ItemName');
            $table->text('Description')->nullable();
            $table->decimal('Price', 10, 2);
            $table->unsignedBigInteger('ClassificationID')->nullable();
            $table->unsignedBigInteger('UnitOfMeasureID')->nullable();
            $table->integer('StocksAvailable')->default(0);
            $table->boolean('IsAvailable')->default(true);
            $table->boolean('IsDeleted')->default(false);
            $table->timestamps();

            // Foreign keys
            if (Schema::hasTable('classifications')) {
                $table->foreign('ClassificationID')->references('ClassificationID')->on('classifications')->onDelete('set null');
            }
            if (Schema::hasTable('unit_of_measures')) {
                $table->foreign('UnitOfMeasureID')->references('UnitOfMeasureID')->on('unit_of_measures')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_items');
    }
}; 