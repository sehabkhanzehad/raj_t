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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('year_id')->constrained()->restrictOnDelete();
            $table->foreignId('pre_registration_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('pilgrim_id')->constrained()->restrictOnDelete();
            $table->foreignId('package_id')->constrained()->restrictOnDelete();
            $table->foreignId('bank_id')->constrained()->restrictOnDelete();


            $table->string('reg_number')->unique(); // MAIN-2026-XXX
            $table->enum('status', ['registered', 'visa_processed', 'flown', 'completed', 'transferred_out', 'cancelled'])->default('registered');

            $table->decimal('contract_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
