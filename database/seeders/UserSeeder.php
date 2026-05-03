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
        $countries = ['KE', 'TZ', 'UG', 'RW'];
        $roles = ['admin', 'hgadmin', 'manager', 'staff'];
        
        // Add Admin User (KE - Kenya)
        User::updateOrCreate(
            ['email' => 'admin@coletha.test'],
            [
                'name' => 'Coletha Admin',
                'full_name' => 'CFTMS System Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'admin',
                'country' => 'KE',
                'timezone' => 'Africa/Nairobi',
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
                'email_verified_at' => now(),
                'role' => 'manager',
                'country' => 'TZ',
                'timezone' => 'Africa/Dar_es_Salaam',
                'is_active' => true,
            ]
        );

        // Add a few staff users for demo with different countries
        $staffData = [
            ['email' => 'staff1@cargo.co.tz', 'name' => 'Kenya Staff', 'country' => 'KE'],
            ['email' => 'staff2@cargo.co.tz', 'name' => 'Tanzania Staff', 'country' => 'TZ'],
            ['email' => 'staff3@cargo.co.tz', 'name' => 'Uganda Staff', 'country' => 'UG'],
            ['email' => 'staff4@cargo.co.tz', 'name' => 'Rwanda Staff', 'country' => 'RW'],
            ['email' => 'staff5@cargo.co.tz', 'name' => 'Burundi Staff', 'country' => 'BU'],
        ];

        foreach ($staffData as $index => $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'full_name' => "Transport Staff " . ($index + 1) . " - {$data['country']}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'role' => 'staff',
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
            'KE' => 'Africa/Nairobi',
            'TZ' => 'Africa/Dar_es_Salaam',
            'UG' => 'Africa/Kampala',
            'RW' => 'Africa/Kigali',
            'BU' => 'Africa/Bujumbura',
        ];
        
        return $timezones[$countryCode] ?? 'UTC';
    }
}
