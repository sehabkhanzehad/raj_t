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
        Schema::table('users', function (Blueprint $table) {
            // Removed global unique constraints
            $table->dropUnique(['nid']);
            $table->dropUnique(['birth_certificate_number']);

            $table->string('country')->after('email')->default('BANGLADESH');

            // Added country-wise document uniqueness (HARD CONSTRAINT)
            // Prevents: Same NID/Birth Certificate in same country
            $table->unique(['country', 'nid'], 'unique_country_nid');
            $table->unique(['country', 'birth_certificate_number'], 'unique_country_birth_cert');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('unique_country_nid');
            $table->dropUnique('unique_country_birth_cert');

            $table->dropColumn('country');

            // Revert to global uniqueness
            $table->unique('nid');
            $table->unique('birth_certificate_number');
        });
    }
};
