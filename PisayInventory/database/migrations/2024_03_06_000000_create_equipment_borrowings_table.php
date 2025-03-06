<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('equipment_borrowings', function (Blueprint $table) {
            $table->string('borrowing_id')->primary();
            $table->string('equipment_id');
            $table->string('borrower_id');
            $table->date('borrow_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->text('purpose');
            $table->text('remarks')->nullable();
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->string('returned_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('equipment_id')->references('equipment_id')->on('equipment');
            $table->foreign('borrower_id')->references('UserAccountID')->on('users');
            $table->foreign('created_by')->references('UserAccountID')->on('users');
            $table->foreign('updated_by')->references('UserAccountID')->on('users');
            $table->foreign('deleted_by')->references('UserAccountID')->on('users');
            $table->foreign('returned_by')->references('UserAccountID')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('equipment_borrowings');
    }
}; 