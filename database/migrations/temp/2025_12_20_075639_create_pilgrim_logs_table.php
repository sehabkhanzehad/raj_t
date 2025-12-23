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
        Schema::create('pilgrim_logs', function (Blueprint $table) {
            $table->foreignId('pilgrim_id')->constrained()->restrictOnDelete();

            // Polymorphic relation (Pre-Reg othoba Main-Reg er history)
            // $table->unsignedBigInteger('reference_id');
            // $table->string('reference_type'); // 'App\Models\PreRegistration' or 'App\Models\MainRegistration'

            $table->string('type'); // 'created', 'transferred', 'converted_to_main', 'expired', 'cancelled'
            $table->text('description')->nullable(); // e.g., "Transferred to Rahim ID: 50"

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilgrim_logs');
    }
};

// tODO: IF PAID AMOUNT THEN ALSO ADD LOG, AND CONNECT WITH TRANSACTIONS 