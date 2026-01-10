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
        Schema::table('pre_registrations', function (Blueprint $table) {
            // Drop the existing foreign key constraint for bank_id
            $table->dropForeign(['bank_id']);

            // Make bank_id nullable
            $table->foreignId('bank_id')->nullable()->change();

            // Recreate the foreign key constraint as nullable
            $table->foreign('bank_id')->references('id')->on('banks')->restrictOnDelete();

            // Add new nullable columns
            $table->string('tracking_no')->nullable()->after('serial_no');
            $table->string('voucher_name')->nullable()->after('bank_voucher_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_registrations', function (Blueprint $table) {
            // Drop the new columns
            $table->dropColumn(['tracking_no', 'voucher_name']);

            // Drop the nullable foreign key constraint
            $table->dropForeign(['bank_id']);

            // Make bank_id not nullable again
            $table->foreignId('bank_id')->nullable(false)->change();

            // Recreate the original foreign key constraint
            $table->foreign('bank_id')->references('id')->on('banks')->restrictOnDelete();
        });
    }
};
