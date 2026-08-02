<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\CargoCheckpoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveTransitTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_live_tracking_data(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $cargo = Cargo::create([
            'tracking_number' => 'TRK-DAR-2026-8890',
            'customer_id' => $customer->id,
            'origin_country' => 'TZ',
            'origin_city' => 'Dar es Salaam',
            'destination_country' => 'TZ',
            'destination_city' => 'Mwanza',
            'status' => Cargo::STATUS_IN_TRANSIT,
        ]);

        $cargo->detail()->create([
            'description' => 'Electronics & Spare Parts',
            'weight_kg' => 250,
            'quantity' => 1,
            'package_count' => 1,
        ]);

        $cargo->checkpoints()->createMany([
            ['name' => 'Dar es Salaam Warehouse', 'status' => 'COMPLETED', 'sequence' => 1, 'timestamp' => now()->subHours(24)],
            ['name' => 'Morogoro Transit Hub', 'status' => 'COMPLETED', 'sequence' => 2, 'timestamp' => now()->subHours(16)],
            ['name' => 'Dodoma Station', 'status' => 'ACTIVE_CURRENT', 'sequence' => 3, 'timestamp' => now()->subHours(5)],
            ['name' => 'Singida Checkpoint', 'status' => 'PENDING', 'sequence' => 4],
            ['name' => 'Mwanza Depot', 'status' => 'PENDING', 'sequence' => 5],
        ]);

        $response = $this->getJson('/api/v1/track/TRK-DAR-2026-8890');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'trackingNumber' => 'TRK-DAR-2026-8890',
                'status' => 'ON_TRANSIT',
                'cargo' => [
                    'description' => 'Electronics & Spare Parts',
                    'weight' => '250.00 kg',
                ],
                'route' => [
                    'progressPercentage' => 60,
                ],
            ]);

        $response->assertJsonCount(5, 'checkpoints');
    }

    public function test_transporter_can_update_checkpoint_and_advance_progress(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $cargo = Cargo::create([
            'tracking_number' => 'TRK-DAR-2026-8891',
            'customer_id' => $customer->id,
            'origin_country' => 'TZ',
            'origin_city' => 'Dar es Salaam',
            'destination_country' => 'TZ',
            'destination_city' => 'Mwanza',
            'status' => Cargo::STATUS_IN_TRANSIT,
        ]);

        $cargo->checkpoints()->createMany([
            ['name' => 'Dar es Salaam Warehouse', 'status' => 'COMPLETED', 'sequence' => 1, 'timestamp' => now()->subHours(24)],
            ['name' => 'Morogoro Transit Hub', 'status' => 'COMPLETED', 'sequence' => 2, 'timestamp' => now()->subHours(16)],
            ['name' => 'Dodoma Station', 'status' => 'ACTIVE_CURRENT', 'sequence' => 3, 'timestamp' => now()->subHours(5)],
            ['name' => 'Singida Checkpoint', 'status' => 'PENDING', 'sequence' => 4],
            ['name' => 'Mwanza Depot', 'status' => 'PENDING', 'sequence' => 5],
        ]);

        // Transporter updates Singida Checkpoint to COMPLETED
        $updateResponse = $this->postJson('/api/v1/shipment/update-checkpoint', [
            'trackingNumber' => 'TRK-DAR-2026-8891',
            'checkpoint_name' => 'Singida Checkpoint',
            'status' => 'COMPLETED',
        ]);

        $updateResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'route' => [
                    'progressPercentage' => 100,
                ],
            ]);

        $this->assertDatabaseHas('cargo_checkpoints', [
            'cargo_id' => $cargo->id,
            'name' => 'Singida Checkpoint',
            'status' => 'COMPLETED',
        ]);
    }

    public function test_can_load_on_track_web_page(): void
    {
        $response = $this->get('/on-track?trackingNumber=TRK-DAR-2026-8890');
        $response->assertStatus(200);
        $response->assertSee('Live Cargo Transit Progress');
    }
}
