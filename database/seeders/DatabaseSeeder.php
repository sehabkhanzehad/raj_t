<?php

namespace Database\Seeders;

use App\Enums\SectionType;
use App\Models\Section;
use App\Models\User;
use App\Models\Year;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('production')) return;

        Year::updateOrCreate([
            'name' => 'Hajj 2026',
        ], [
            "start_date" => "2025-06-01",
            "end_date" => "2026-05-31",
            "status" => true,
        ]);

        User::updateOrCreate(
            ['email' => 'user@email.com'],
            [
                'first_name' => 'Raj',
                'last_name' => 'Travels',
                'username' => 'rajtravels',
                'password' => 'password',
                'gender' => 'male',
            ]
        );

        Section::updateOrCreate(
            ['code' => '205.00'],
            [
                'name' => 'Lending & Collection',
                'type' => SectionType::Lend,
                'description' => 'Lending & Collection Section',
            ]
        );

        Section::updateOrCreate(
            ['code' => '101.00'],
            [
                'name' => 'Borrowing & Payment',
                'type' => SectionType::Borrow,
                'description' => 'Borrowing & Payment Section',
            ]
        );

        Section::updateOrCreate([
            'type' => SectionType::PreRegistration,
        ], [
            'code' => '402.00',
            'name' => 'Pre Registration',
            'description' => 'Pre Registration Section.',
        ]);

        Section::updateOrCreate([
            'type' => SectionType::Registration,
        ], [
            'name' => 'Registration',
            'description' => 'Registration Section.',
        ]);
    }
}
