<?php

namespace Database\Seeders;

use App\Enums\SectionType;
use App\Models\Section;
use App\Models\User;
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
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
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
    }
}
