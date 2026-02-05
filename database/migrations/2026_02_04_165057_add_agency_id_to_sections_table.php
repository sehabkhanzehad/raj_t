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
            // Drop the unique constraint on code
            $table->dropUnique(['code']);

            // Add agency_id column
            $table->foreignUuid('agency_id')->nullable()->after('id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Add composite unique constraint on agency_id and code
            $table->unique(['agency_id', 'code'], 'sections_agency_id_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            // Drop composite unique constraint
            $table->dropUnique('sections_agency_id_code_unique');

            // Drop foreign key and column
            $table->dropForeign(['agency_id']);
            $table->dropColumn('agency_id');

            // Restore unique constraint on code
            $table->unique('code');
        });
    }
};
