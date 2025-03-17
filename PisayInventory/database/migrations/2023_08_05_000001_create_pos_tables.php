<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create students table if it doesn't exist
        if (!Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table) {
                $table->id('StudentID');
                $table->string('StudentNumber', 20)->unique();
                $table->string('FirstName', 50);
                $table->string('LastName', 50);
                $table->string('MiddleName', 50)->nullable();
                $table->unsignedBigInteger('SectionID')->nullable();
                $table->enum('Gender', ['Male', 'Female', 'Other'])->default('Other');
                $table->enum('Status', ['Active', 'Inactive', 'Graduated', 'Transferred'])->default('Active');
                
                // Add foreign key if sections table exists
                if (Schema::hasTable('sections')) {
                    $table->foreign('SectionID')->references('SectionID')->on('sections')->onDelete('set null');
                }
            });
        }
        
        // Create POS orders table
        Schema::create('pos_orders', function (Blueprint $table) {
            $table->id('OrderID');
            $table->dateTime('OrderDate');
            $table->decimal('TotalAmount', 10, 2);
            $table->enum('PaymentMethod', ['cash', 'deposit'])->default('cash');
            $table->enum('Status', ['Pending', 'Completed', 'Cancelled'])->default('Pending');
            $table->unsignedBigInteger('StudentID')->nullable();
            $table->decimal('AmountTendered', 10, 2)->nullable();
            $table->decimal('Change', 10, 2)->nullable();
            $table->dateTime('CompletedDate')->nullable();
            $table->unsignedBigInteger('ProcessedBy')->nullable();
            $table->string('Remarks', 255)->nullable();
            
            // Foreign keys
            if (Schema::hasTable('students')) {
                $table->foreign('StudentID')->references('StudentID')->on('students')->onDelete('set null');
            }
            
            if (Schema::hasTable('users')) {
                $table->foreign('ProcessedBy')->references('id')->on('users')->onDelete('set null');
            }
        });
        
        // Create POS order items table
        Schema::create('pos_order_items', function (Blueprint $table) {
            $table->id('OrderItemID');
            $table->unsignedBigInteger('OrderID');
            $table->unsignedBigInteger('ItemID');
            $table->integer('Quantity');
            $table->decimal('UnitPrice', 10, 2);
            $table->decimal('Subtotal', 10, 2);
            
            // Foreign keys
            $table->foreign('OrderID')->references('OrderID')->on('pos_orders')->onDelete('cascade');
            
            if (Schema::hasTable('items')) {
                $table->foreign('ItemID')->references('ItemID')->on('items')->onDelete('cascade');
            }
        });
        
        // Create cash deposits table
        Schema::create('cash_deposits', function (Blueprint $table) {
            $table->id('DepositID');
            $table->unsignedBigInteger('StudentID');
            $table->decimal('Amount', 10, 2); // Can be positive (deposit) or negative (payment)
            $table->dateTime('TransactionDate');
            $table->string('Description', 255)->nullable();
            $table->enum('TransactionType', ['Deposit', 'Payment', 'Refund', 'Adjustment'])->default('Deposit');
            $table->unsignedBigInteger('ProcessedBy')->nullable();
            $table->string('Remarks', 255)->nullable();
            
            // Foreign keys
            if (Schema::hasTable('students')) {
                $table->foreign('StudentID')->references('StudentID')->on('students')->onDelete('cascade');
            }
            
            if (Schema::hasTable('users')) {
                $table->foreign('ProcessedBy')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_deposits');
        Schema::dropIfExists('pos_order_items');
        Schema::dropIfExists('pos_orders');
        
        // Don't drop students table here as it might be used by other parts of the application
    }
} 