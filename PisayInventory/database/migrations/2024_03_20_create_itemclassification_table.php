<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('itemclassification', function (Blueprint $table) {
            $table->id('ClassificationID');
            $table->string('ClassificationName');
            $table->boolean('IsDeleted')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('itemclassification');
    }
}; 