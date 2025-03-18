<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_deposits', function (Blueprint $table) {
            $table->id('DepositId');
            $table->string('StudentID');
            $table->dateTime('TransactionDate');
            $table->string('ReferenceNumber');
            $table->string('TransactionType');
            $table->decimal('Amount', 10, 2);
            $table->decimal('BalanceBefore', 10, 2);
            $table->decimal('BalanceAfter', 10, 2);
            $table->string('Notes')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->timestamp('CreatedAt')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->timestamp('ModifiedAt')->nullable();
            $table->boolean('IsDeleted')->default(0);
            
            // Foreign key relationship
            $table->foreign('StudentID')->references('StudentID')->on('students');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_deposits');
    }
} 