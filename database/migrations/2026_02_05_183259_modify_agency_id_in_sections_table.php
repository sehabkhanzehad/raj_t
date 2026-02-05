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
        Schema::table('sections', function (Blueprint $table) {
            // Make agency_id NOT NULL
            $table->uuid('agency_id')->nullable(false)->change();

            // Add foreign key constraint
            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['agency_id']);

            // Make agency_id nullable again
            $table->uuid('agency_id')->nullable()->change();
        });
    }
};
