<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_deposits', function (Blueprint $table) {
            $table->id('DepositID');
            $table->string('StudentID');
            $table->decimal('Amount', 10, 2);
            $table->string('TransactionType')->default('deposit'); // deposit or payment
            $table->text('Description')->nullable();
            $table->foreignId('ProcessedBy')->constrained('users', 'id');
            $table->boolean('IsDeleted')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_deposits');
    }
}; 