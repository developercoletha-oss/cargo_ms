<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates admin user with custom email and password.
     * 
     * Usage:
     *   php artisan db:seed --class=AdminSeeder
     * 
     * Default credentials:
     *   Email: admin@cftms.test
     *   Password: admin123
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@cftms.test');
        $password = env('ADMIN_PASSWORD', 'admin123');

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'System Administrator',
                'full_name' => 'CFTMS Admin User',
                'password' => Hash::make($password),
                'role' => 'admin',
                'country' => 'TZ',
                'timezone' => 'Africa/Dar_es_Salaam',
                'is_active' => true,
            ]
        );

        $this->command->info('Admin user created/updated successfully!');
        $this->command->info('Email: ' . $email);
        $this->command->info('Password: ' . $password);
        $this->command->info('Role: admin');
        $this->command->info('========================================');
        $this->command->info('Login at: http://127.0.0.1:8000/login');
        $this->command->info('========================================');
    }
}
