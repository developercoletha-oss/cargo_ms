<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['email' => 'admin@coletha.test', 'name' => 'System Admin', 'full_name' => 'System Administrator', 'role' => 'admin'],
            ['email' => 'manager@coletha.test', 'name' => 'Operations Manager', 'full_name' => 'Operations Manager', 'role' => 'manager'],
            ['email' => 'customer@coletha.test', 'name' => 'Default Customer', 'full_name' => 'Default Customer Account', 'role' => 'customer'],
            ['email' => 'transporter@coletha.test', 'name' => 'Default Transporter', 'full_name' => 'Default Transporter Account', 'role' => 'transporter'],
            ['email' => 'storekeeper@coletha.test', 'name' => 'Default Store Keeper', 'full_name' => 'Default Store Keeper Account', 'role' => 'store_keeper'],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'full_name' => $userData['full_name'],
                    'password' => Hash::make('password'),
                    'role' => $userData['role'],
                    'country' => 'TZ',
                    'timezone' => 'Africa/Dar_es_Salaam',
                    'is_active' => true,
                ]
            );
        }
    }
}
