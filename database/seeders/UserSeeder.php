<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add Admin User
        User::updateOrCreate(
            ['email' => 'admin@coletha.test'],
            [
                'name' => 'Coletha Admin',
                'full_name' => 'CFTMS System Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Add Manager User
        User::updateOrCreate(
            ['email' => 'manager@coletha.test'],
            [
                'name' => 'Operations Manager',
                'full_name' => 'Transport Operations Manager',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Add a few staff users for demo
        for ($i = 1; $i <= 5; $i++) {
            User::updateOrCreate(
                ['email' => "staff{$i}@cargo.co.tz"],
                [
                    'name' => "Staff Member {$i}",
                    'full_name' => "Transport Staff {$i}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
