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
            $table->date('cancel_date')->nullable()->after('archive_date');
            $table->date('transfer_date')->nullable()->after('cancel_date');
            $table->text('transfer_note')->nullable()->after('transfer_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_registrations', function (Blueprint $table) {
            $table->dropColumn(['cancel_date', 'transfer_date', 'transfer_note']);
        });
    }
};
