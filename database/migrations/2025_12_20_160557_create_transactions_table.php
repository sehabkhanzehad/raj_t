<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('year_id')->constrained()->restrictOnDelete();
            $table->foreignId('section_id')->constrained()->restrictOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained()->restrictOnDelete();

            // $table->foreignId('registration_id')->nullable()->constrained();

            $table->enum('type', ['income', 'expense']); // Credit=Income/Opening, Debit=Expense/Loan Paid
            $table->decimal('amount', 15, 2);
            $table->decimal('after_balance', 15, 2);
            $table->decimal('before_balance', 15, 2);
            $table->date('date');

            $table->text('description')->nullable();
            $table->string('voucher_no')->nullable();

            // $table->string('reference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
