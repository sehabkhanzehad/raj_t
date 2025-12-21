<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\SectionType;
use App\Enums\UserRole;
use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $sections = [
            // [
            //     'code' => '201.1',
            //     'name' => 'প্রাইম ব্যাংক লিমিটেড',
            //     'type' => SectionType::Bank,
            //     'branch' => 'Dhaka',
            //     'account_number' => '123456789',
            //     'account_holder_name' => 'John Doe',
            //     'address' => '123 Main St, City, Country',
            // ],
            // [
            //     'code' => '201.2',
            //     'name' => 'রুপালী ব্যাংক লিমিটেড',
            //     'type' => SectionType::Bank,
            //     'branch' => 'Dhaka',
            //     'account_number' => '123456789',
            //     'account_holder_name' => 'John Doe',
            //     'address' => '123 Main St, City, Country',
            // ],
            // [
            //     'code' => '201.3',
            //     'name' => 'ইসলামী ব্যাংক বাংলাদেশ লিমিটেড',
            //     'type' => SectionType::Bank,
            //     'branch' => 'Dhaka',
            //     'account_number' => '123456789',
            //     'account_holder_name' => 'John Doe',
            //     'address' => '123 Main St, City, Country',
            // ],
            // [
            //     'code' => '201.4',
            //     'name' => 'এক্সিম ব্যাংক লিমিটেড',
            //     'type' => SectionType::Bank,
            //     'branch' => 'Dhaka',
            //     'account_number' => '123456789',
            //     'account_holder_name' => 'John Doe',
            //     'address' => '123 Main St, City, Country',
            // ],
            // [
            //     'code' => '201.5',
            //     'name' => 'সোনালী ব্যাংক লিমিটেড',
            //     'type' => SectionType::Bank,
            //     'branch' => 'Dhaka',
            //     'account_number' => '123456789',
            //     'account_holder_name' => 'John Doe',
            //     'address' => '123 Main St, City, Country',
            // ],
            // [
            //     'code' => '201.6',
            //     'name' => 'এনআরবি ব্যাংক লিমিটেড (৫৬)',
            //     'type' => SectionType::Bank,
            //     'branch' => 'Dhaka',
            //     'account_number' => '123456789',
            //     'account_holder_name' => 'John Doe',
            //     'address' => '123 Main St, City, Country',
            // ],
            // [
            //     'code' => '201.7',
            //     'name' => 'এনআরবি ব্যাংক লিমিটেড (৪২)',
            //     'type' => SectionType::Bank,
            //     'branch' => 'Dhaka',
            //     'account_number' => '123456789',
            //     'account_holder_name' => 'John Doe',
            //     'address' => '123 Main St, City, Country',
            // ],
            // [
            //     'code' => '201.8',
            //     'name' => 'প্রিমিয়ার ব্যাংক লিমিটেড',
            //     'type' => SectionType::Bank,
            //     'branch' => 'Dhaka',
            //     'account_number' => '123456789',
            //     'account_holder_name' => 'John Doe',
            //     'address' => '123 Main St, City, Country',
            // ],
            // [
            //     'code' => '201.9',
            //     'name' => 'আল আরাফাহ ইসলামী ব্যাংক লিমিটেড',
            //     'type' => SectionType::Bank,
            //     'branch' => 'Dhaka',
            //     'account_number' => '123456789',
            //     'account_holder_name' => 'John Doe',
            //     'address' => '123 Main St, City, Country',
            // ],
            [
                'code' => '301.1',
                'name' => 'Group Leader Zone A',
                'type' => SectionType::GroupLeader,
                'leader_name' => 'Jamal Uddin',
                'email' => 'jamal@example.com',
                'phone' => '01710000001',
                'address' => 'Dhaka, Bangladesh',
            ],
            [
                'code' => '301.2',
                'name' => 'Group Leader Zone B',
                'type' => SectionType::GroupLeader,
                'leader_name' => 'Rahim Mia',
                'email' => 'rahim@example.com',
                'phone' => '01710000002',
                'address' => 'Chattogram, Bangladesh',
            ],
            [
                'code' => '301.3',
                'name' => 'Group Leader Zone C',
                'type' => SectionType::GroupLeader,
                'leader_name' => 'Kamal Hossain',
                'email' => 'kamal@example.com',
                'phone' => '01710000003',
                'address' => 'Sylhet, Bangladesh',
            ],
            [
                'code' => '301.4',
                'name' => 'Group Leader Zone D',
                'type' => SectionType::GroupLeader,
                'leader_name' => 'Jahirul Islam',
                'email' => 'jahirul@example.com',
                'phone' => '01710000004',
                'address' => 'Rangpur, Bangladesh',
            ],
            [
                'code' => '301.5',
                'name' => 'Group Leader Zone E',
                'type' => SectionType::GroupLeader,
                'leader_name' => 'Mehedi Hasan',
                'email' => 'mehedi@example.com',
                'phone' => '01710000005',
                'address' => 'Khulna, Bangladesh',
            ],
            [
                'code' => '301.6',
                'name' => 'Group Leader Zone F',
                'type' => SectionType::GroupLeader,
                'leader_name' => 'Nurul Islam',
                'email' => 'nurul@example.com',
                'phone' => '01710000006',
                'address' => 'Barisal, Bangladesh',
            ],
            [
                'code' => '101.0',
                'name' => 'Borrowing & Repaying',
                'type' => SectionType::Loan,
            ],
            [
                'code' => '205.0',
                'name' => 'Lending & Collection',
                'type' => SectionType::Loan,
            ],

        ];
        foreach ($sections as $item) {
            DB::transaction(function () use ($item) {
                $section = Section::create([
                    'code' => $item['code'],
                    'name' => $item['name'],
                    'type' => $item['type'],
                ]);

                if ($item['type'] === SectionType::Bank) {
                    $section->bank()->create([
                        'name' => $item['name'],
                        'branch' => $item['branch'],
                        'account_number' => $item['account_number'],
                        'account_holder_name' => $item['account_holder_name'],
                        'address' => $item['address'],
                        'routing_number' => '987654321',
                        'swift_code' => 'XYZBBDDH',
                        'opening_date' => now(),
                        'account_type' => AccountType::Current,
                        'phone' => '01700000000',
                        'telephone' => '02-7654321',
                        'email' => 'bank@example.com',
                        'website' => 'https://examplebank.com',
                        'status' => true,
                    ]);
                } elseif ($item['type'] === SectionType::GroupLeader) {
                    $section->groupLeader()->create([
                        'name' => $item['leader_name'],
                        'role' => UserRole::GroupLeader,
                        'email' => $item['email'],
                        'phone' => $item['phone'],
                        'address' => $item['address'],
                    ]);
                }
            });
        }
    }
}
