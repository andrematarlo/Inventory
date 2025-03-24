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
            $table->integer('StocksAvailable')->default(0);
            $table->unsignedBigInteger('ClassificationId');
            $table->boolean('IsDeleted')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ClassificationId')
                  ->references('ClassificationId')
                  ->on('Classifications');
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_items');
    }
}; 