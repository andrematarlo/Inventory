<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('classifications', function (Blueprint $table) {
            $table->id('ClassificationID');
            $table->string('ClassificationName');
            $table->boolean('IsDeleted')->default(false);
            $table->timestamp('DateCreated')->useCurrent();
            $table->timestamp('DateModified')->nullable();
            $table->timestamp('DateDeleted')->nullable();
            $table->timestamp('DateRestored')->nullable();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('classifications');
    }
}; 