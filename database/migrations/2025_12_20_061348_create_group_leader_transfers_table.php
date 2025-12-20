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
        Schema::create('group_leader_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pilgrim_id')->constrained()->restrictOnDelete();
            $table->foreignId('pre_registration_id')->constrained()->restrictOnDelete();
            $table->foreignId('from_leader_id')->constrained('group_leaders')->restrictOnDelete();
            $table->foreignId('to_leader_id')->constrained('group_leaders')->restrictOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_leader_transfers');
    }
};
