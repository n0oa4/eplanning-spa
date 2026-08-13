<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@eplanning.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        // Kabid
        $kabid = User::firstOrCreate(
            ['email' => 'kabid@eplanning.com'],
            [
                'name' => 'Kepala Bidang',
                'password' => Hash::make('password'),
            ]
        );
        $kabid->assignRole('kabid');

        // 6 Operator
        $operators = [
            ['name' => 'Operator 1', 'email' => 'operator1@eplanning.com'],
            ['name' => 'Operator 2', 'email' => 'operator2@eplanning.com'],
            ['name' => 'Operator 3', 'email' => 'operator3@eplanning.com'],
            ['name' => 'Operator 4', 'email' => 'operator4@eplanning.com'],
            ['name' => 'Operator 5', 'email' => 'operator5@eplanning.com'],
            ['name' => 'Operator 6', 'email' => 'operator6@eplanning.com'],
        ];

        foreach ($operators as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                ]
            );
            $user->assignRole('operator');
        }
    }
}
