<?php

namespace Database\Seeders;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->info('No users found. Please run UserSeeder first.');
            return;
        }

        $countries = ['KE', 'TZ', 'UG', 'RW', 'BU'];
        $statuses = ['pending', 'in_transit', 'delivered', 'cancelled'];
        $priorities = ['low', 'normal', 'high', 'urgent'];
        
        $shipments = [];
        
        // Create 50 sample shipments
        for ($i = 0; $i < 50; $i++) {
            $originCountry = $countries[array_rand($countries)];
            $destCountry = $countries[array_rand($countries)];
            while ($destCountry === $originCountry) {
                $destCountry = $countries[array_rand($countries)];
            }
            
            $status = $statuses[array_rand($statuses)];
            $priority = $priorities[array_rand($priorities)];
            
            $createdAt = now()->subDays(rand(1, 60));
            
            $shipments[] = [
                'tracking_number' => 'CFTMS-' . strtoupper(uniqid()),
                'sender_id' => $users->random()->id,
                'receiver_id' => $users->random()->id,
                'origin_country' => $originCountry,
                'destination_country' => $destCountry,
                'origin_city' => $this->getCityForCountry($originCountry),
                'destination_city' => $this->getCityForCountry($destCountry),
                'description' => 'Cargo shipment #' . ($i + 1),
                'weight' => rand(10, 2000) + (rand(0, 99) / 100),
                'status' => $status,
                'priority' => $priority,
                'estimated_delivery' => $createdAt->copy()->addDays(rand(5, 30)),
                'actual_delivery' => $status === 'delivered' ? $createdAt->copy()->addDays(rand(5, 30)) : null,
                'assigned_to' => rand(0, 1) ? $users->random()->id : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }
        
        Shipment::insert($shipments);
        
        $this->command->info('Created ' . count($shipments) . ' sample shipments.');
    }
    
    private function getCityForCountry(string $countryCode): string
    {
        $cities = [
            'KE' => ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret'],
            'TZ' => ['Dar es Salaam', 'Arusha', 'Mwanza', 'Dodoma', 'Zanzibar'],
            'UG' => ['Kampala', 'Entebbe', 'Gulu', 'Jinja', 'Mbarara'],
            'RW' => ['Kigali', 'Butare', 'Gitarama', 'Ruhengeri'],
            'BU' => ['Bujumbura', 'Gitega', 'Ngozi', 'Ruyigi'],
        ];
        
        return $cities[$countryCode][array_rand($cities[$countryCode] ?? ['Unknown'])];
    }
}
