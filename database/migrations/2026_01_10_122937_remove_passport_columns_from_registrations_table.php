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
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex('registrations_status_passport_number_passport_expiry_date_index');

            $table->dropColumn([
                'passport_number',
                'passport_expiry_date',
            ]);

            $table->index('status', 'registrations_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('passport_number')->after('date');
            $table->date('passport_expiry_date')->after('passport_number');

            $table->dropIndex('registrations_status_index');

            $table->index([
                'status',
                'passport_number',
                'passport_expiry_date',
            ], 'registrations_status_passport_number_passport_expiry_date_index');
        });
    }
};
