<?php

namespace Database\Seeders;

use App\Enums\CustomerRole;
use App\Enums\SectionType;
use App\Models\Customer;
use App\Models\Section;
use App\Models\User;
use App\Models\Year;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Pest\ArchPresets\Custom;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $customer =  Customer::updateOrCreate(
            ['email' => 'skzehad@gmail.com'],
            [
                'name' => 'Sk Zehad',
                'role' => CustomerRole::Customer,
                'password' => '1235412354',
            ]
        );

        $customer->agency()->updateOrCreate([], [
            'name' => 'Sk Zehad Travels',
            'license' => '0935',
            'address' => 'Nayagola, Nayagola Hat-6300, Chapainawabganj, Rajshahi, Dhaka, Bangladesh',
            'phone' => '+8801744646344',
            'email' => 'skzehad@gmail.com',
        ]);

        if (app()->environment('production')) return;

        $this->createCustomerOne();
        $this->createCustomerTwo();

        Year::updateOrCreate([
            'name' => 'Hajj 2026',
        ], [
            "start_date" => "2025-06-01",
            "end_date" => "2026-05-31",
            "status" => true,
        ]);



        // Year::updateOrCreate([
        //     'name' => 'Hajj 2026',
        // ], [
        //     "start_date" => "2025-06-01",
        //     "end_date" => "2026-05-31",
        //     "status" => true,
        // ]);

        // User::updateOrCreate(
        //     ['email' => 'user@email.com'],
        //     [
        //         'first_name' => 'Raj',
        //         'last_name' => 'Travels',
        //         'username' => 'rajtravels',
        //         'password' => 'password',
        //         'gender' => 'male',
        //     ]
        // );

        // Section::updateOrCreate(
        //     ['code' => '205.00'],
        //     [
        //         'name' => 'Lending & Collection',
        //         'type' => SectionType::Lend,
        //         'description' => 'Lending & Collection Section',
        //     ]
        // );

        // Section::updateOrCreate(
        //     ['code' => '101.00'],
        //     [
        //         'name' => 'Borrowing & Payment',
        //         'type' => SectionType::Borrow,
        //         'description' => 'Borrowing & Payment Section',
        //     ]
        // );

        // Section::updateOrCreate([
        //     'type' => SectionType::PreRegistration,
        // ], [
        //     'code' => '402.00',
        //     'name' => 'Pre Registration',
        //     'description' => 'Pre Registration Section.',
        // ]);

        // Section::updateOrCreate([
        //     'type' => SectionType::Registration,
        // ], [
        //     'name' => 'Registration',
        //     'description' => 'Registration Section.',
        // ]);
    }

    private function createCustomerOne(): void
    {
        $customer =  Customer::updateOrCreate(
            ['email' => 'customer1@email.com'],
            [
                'name' => 'Customer One',
                'role' => CustomerRole::Customer,
                'password' => 'password',
            ]
        );

        $agency = $customer->agency()->updateOrCreate([], [
            'name' => 'M/S RAJ TRAVELS',
            'license' => '0935',
            'address' => '189/1, Nayagola, Nayagola Hat-6300, Chapainawabganj, Rajshahi, Dhaka, Bangladesh',
            'phone' => '+8801799745020',
            'email' => 'info@msrajtravels.com',
        ]);

        $agency->teamMembers()->updateOrCreate([
            'email' => 'team_member1@email.com',
        ], [
            'name' => 'Team Member One',
            'role' => CustomerRole::TeamMember,
            'password' => 'password',
        ]);
    }

    private function createCustomerTwo(): void
    {
        $customer =  Customer::updateOrCreate(
            ['email' => 'customer2@email.com'],
            [
                'name' => 'Customer Two',
                'role' => CustomerRole::Customer,
                'password' => 'password',
            ]
        );

        $agency = $customer->agency()->updateOrCreate([], [
            'name' => 'SK TRAVELS',
            'license' => '1111',
            'address' => '189/1, Nayagola, Nayagola Hat-6300, Chapainawabganj, Rajshahi, Dhaka, Bangladesh',
            'phone' => '++8801744646344',
            'email' => 'info@sktravels.com',
        ]);

        $agency->teamMembers()->updateOrCreate([
            'email' => 'team_member2@email.com',
        ], [
            'name' => 'Team Member Two',
            'role' => CustomerRole::TeamMember,
            'password' => 'password',
        ]);

        $groupLeaderSection = Section::UpdateOrCreate(
            ['code' => '301.01', 'agency_id' => $agency->id],
            [

                'name' => 'Abdul Ajij',
                'type' => SectionType::GroupLeader,
                'description' => 'Group Leader Section for Abdul Ajij',
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'ajij@email.com'],
            [
                'first_name' => 'Abdul',
                'last_name' => 'Ajij',
                'full_name' => 'Abdul Ajij',
                'first_name_bangla' => 'আব্দুল',
                'last_name_bangla' => 'আজিজ',
                'full_name_bangla' => 'আব্দুল আজিজ',
                'mother_name' => 'Rokeya Begum',
                'father_name' => 'Karim Uddin',
                'mother_name_bangla' => 'রোকেয়া বেগম',
                'father_name_bangla' => 'করিম উদ্দিন',
                'username' => 'ajij',
                'phone' => '+8801712345678',
                'gender' => 'male',
                'is_married' => false,
                'nid' => '1980501234567',
                'date_of_birth' => '1985-05-15',
                'occupation' => 'Business',
            ]
        );

        $groupLeaderSection->groupLeader()->updateOrCreate([
            'agency_id' => $agency->id,
            'user_id' => $user->id,
        ], [
            'group_name' => 'Ajij Group',
        ]);
    }
}
