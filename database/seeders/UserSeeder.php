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
        // Add Admin User (TZ - Tanzania)
        User::updateOrCreate(
            ['email' => 'admin@coletha.test'],
            [
                'name' => 'Coletha Admin',
                'full_name' => 'CFTMS System Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'country' => 'TZ',
                'timezone' => 'Africa/Dar_es_Salaam',
                'is_active' => true,
            ]
        );

        // Add Manager User (TZ - Tanzania)
        User::updateOrCreate(
            ['email' => 'manager@coletha.test'],
            [
                'name' => 'Operations Manager',
                'full_name' => 'Transport Operations Manager',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'country' => 'TZ',
                'timezone' => 'Africa/Dar_es_Salaam',
                'is_active' => true,
            ]
        );

        // Add a few transporter users for demo
        $staffData = [
            ['email' => 'staff1@cargo.co.tz', 'name' => 'Tanzania Staff 1', 'country' => 'TZ'],
            ['email' => 'staff2@cargo.co.tz', 'name' => 'Tanzania Staff', 'country' => 'TZ'],
            ['email' => 'staff3@cargo.co.tz', 'name' => 'Tanzania Staff 3', 'country' => 'TZ'],
            ['email' => 'staff4@cargo.co.tz', 'name' => 'Tanzania Staff 4', 'country' => 'TZ'],
            ['email' => 'staff5@cargo.co.tz', 'name' => 'Tanzania Staff 5', 'country' => 'TZ'],
        ];

        foreach ($staffData as $index => $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'full_name' => "Transport Staff " . ($index + 1) . " - {$data['country']}",
                    'password' => Hash::make('password'),
                    'role' => 'transporter',
                    'country' => $data['country'],
                    'timezone' => $this->getTimezoneForCountry($data['country']),
                    'is_active' => true,
                ]
            );
        }
    }
    
    private function getTimezoneForCountry(string $countryCode): string
    {
        $timezones = [
            'TZ' => 'Africa/Dar_es_Salaam',
        ];
        
        return $timezones[$countryCode] ?? 'UTC';
    }
}
