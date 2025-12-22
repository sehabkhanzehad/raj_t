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
        Schema::create('umrahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('year_id')->constrained()->restrictOnDelete();
            $table->foreignId('group_leader_id')->constrained()->restrictOnDelete();
            $table->foreignId('pilgrim_id')->constrained()->restrictOnDelete();
            $table->foreignId('package_id')->constrained()->restrictOnDelete();

            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            // allow entry umrah haji without package id

            // $table->string('booking_reference')->unique()->nullable();
            // $table->date('departure_date')->nullable();
            // $table->date('return_date')->nullable();
            // $table->decimal('total_amount', 10, 2)->default(0);
            // $table->decimal('paid_amount', 10, 2)->default(0);
            // $table->decimal('remaining_amount', 10, 2)->default(0);
            // $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');
            // $table->text('special_requirements')->nullable();
            // $table->text('notes')->nullable();
            // $table->string('visa_status')->nullable();
            // $table->date('visa_issue_date')->nullable();
            // $table->date('visa_expiry_date')->nullable();
            // $table->string('flight_details')->nullable();
            // $table->string('hotel_details')->nullable();
            // $table->boolean('is_active')->default(true);
            // $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umrahs');
    }
};
