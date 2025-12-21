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


            $table->foreignId('registration_id')->nullable()->constrained();

            // লোন বা ধার এর জন্য আলাদা ট্র্যাকিং (Optional)
            $table->string('contact_person')->nullable(); // কার থেকে ধার নেয়া হয়েছে

            $table->enum('type', ['credit', 'debit']); // Credit=Income/Opening, Debit=Expense/Loan Paid
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->string('reference')->nullable(); // Voucher No / Trx ID
            $table->text('description')->nullable();
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
