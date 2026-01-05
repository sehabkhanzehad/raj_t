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
        Schema::table('pilgrim_logs', function (Blueprint $table) {
            $table->string('status_from')->nullable()->after('description'); // Old status (if status changed)
            $table->string('status_to')->nullable()->after('status_from'); // New status (if status changed)

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilgrim_logs', function (Blueprint $table) {
            $table->dropColumn('status_from');
            $table->dropColumn('status_to');
        });
    }
};
