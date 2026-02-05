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
        Schema::table('group_leaders', function (Blueprint $table) {
            // Drop the foreign key constraint on user_id first
            $table->dropForeign(['user_id']);

            // Drop the unique constraint on user_id
            $table->dropUnique(['user_id']);

            // Recreate the foreign key without unique
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            // Add agency_id column
            $table->foreignUuid('agency_id')->nullable()->after('id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Add composite unique constraint on agency_id and user_id
            $table->unique(['agency_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_leaders', function (Blueprint $table) {
            // Drop composite unique constraint
            $table->dropUnique(['agency_id', 'user_id']);

            // Drop foreign key and column
            $table->dropForeign(['agency_id']);
            $table->dropColumn('agency_id');

            // Drop the foreign key on user_id
            $table->dropForeign(['user_id']);

            // Restore unique constraint on user_id with foreign key
            $table->foreignId('user_id')->unique()->change()->constrained()->restrictOnDelete();
        });
    }
};
