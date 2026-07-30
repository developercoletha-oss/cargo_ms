<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\TransportStaff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegionalHubCheckpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_transporter_can_update_related_route_cargo_at_regional_hub(): void
    {
        $transporter = User::factory()->create(['role' => 'transporter']);
        $customer = User::factory()->create(['role' => 'customer']);
        $transportStaff = TransportStaff::create([
            'user_id' => $transporter->id,
            'staff_code' => 'TSF-0001',
            'is_active' => true,
        ]);

        $selectedCargo = $this->cargoFor($customer, $transportStaff, 'Dar es Salaam', 'Arusha');
        $sameRouteCargo = $this->cargoFor($customer, $transportStaff, 'Dar es Salaam', 'Kilimanjaro');
        $otherRouteCargo = $this->cargoFor($customer, $transportStaff, 'Lindi', 'Mtwara');

        $response = $this->actingAs($transporter)->postJson(
            route('dashboard.cargo.regional-hub-checkpoint', $selectedCargo),
            [
                'latitude' => -6.8278,
                'longitude' => 37.6591,
                'location_name' => 'Morogoro Regional Hub',
                'update_related' => true,
            ]
        );

        $response
            ->assertOk()
            ->assertJson([
                'updated_count' => 2,
                'location_name' => 'Morogoro Regional Hub',
            ]);

        $this->assertSame(Cargo::STATUS_ARRIVED_REGIONAL_HUB, $selectedCargo->fresh()->status);
        $this->assertSame(Cargo::STATUS_ARRIVED_REGIONAL_HUB, $sameRouteCargo->fresh()->status);
        $this->assertSame(Cargo::STATUS_IN_TRANSIT, $otherRouteCargo->fresh()->status);
        $this->assertDatabaseCount('cargo_location_updates', 2);
    }

    private function cargoFor(User $customer, TransportStaff $transportStaff, string $origin, string $destination): Cargo
    {
        return Cargo::create([
            'customer_id' => $customer->id,
            'transport_staff_id' => $transportStaff->id,
            'origin_country' => 'tz',
            'origin_city' => $origin,
            'destination_country' => 'tz',
            'destination_city' => $destination,
            'description' => 'Test cargo',
            'status' => Cargo::STATUS_IN_TRANSIT,
            'signed_by_transporter' => $transportStaff->user_id,
            'signed_at' => now(),
            'handover_confirmed_at' => now(),
        ]);
    }
}
