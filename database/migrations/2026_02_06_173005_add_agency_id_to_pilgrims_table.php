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
        Schema::table('pilgrims', function (Blueprint $table) {
            $table->foreignUuid('agency_id')->nullable()->after('id')->constrained('agencies')->cascadeOnUpdate()->restrictOnDelete();
            $table->unique(['user_id', 'agency_id'], 'pilgrim_user_agency_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilgrims', function (Blueprint $table) {
            $table->dropUnique('pilgrim_user_agency_unique');
            $table->dropForeign(['agency_id']);
            $table->dropColumn('agency_id');
        });
    }
};
