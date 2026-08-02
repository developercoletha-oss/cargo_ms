<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\CargoCheckpoint;
use Illuminate\Database\Seeder;

class CargoCheckpointSeeder extends Seeder
{
    /**
     * Seed sample checkpoints for existing cargo records.
     */
    public function run(): void
    {
        $cargoes = Cargo::query()->get();

        foreach ($cargoes as $cargo) {
            if ($cargo->checkpoints()->exists()) {
                continue;
            }

            $origin = $cargo->origin_city ?: 'Dar es Salaam';
            $destination = $cargo->destination_city ?: 'Mwanza';

            $checkpoints = [
                [
                    'cargo_id' => $cargo->id,
                    'name' => "{$origin} Warehouse",
                    'status' => CargoCheckpoint::STATUS_COMPLETED,
                    'sequence' => 1,
                    'timestamp' => now()->subHours(24),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'cargo_id' => $cargo->id,
                    'name' => 'Morogoro Transit Hub',
                    'status' => CargoCheckpoint::STATUS_COMPLETED,
                    'sequence' => 2,
                    'timestamp' => now()->subHours(16),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'cargo_id' => $cargo->id,
                    'name' => 'Dodoma Station',
                    'status' => CargoCheckpoint::STATUS_ACTIVE_CURRENT,
                    'sequence' => 3,
                    'timestamp' => now()->subHours(5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'cargo_id' => $cargo->id,
                    'name' => 'Singida Checkpoint',
                    'status' => CargoCheckpoint::STATUS_PENDING,
                    'sequence' => 4,
                    'timestamp' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'cargo_id' => $cargo->id,
                    'name' => "{$destination} Depot",
                    'status' => CargoCheckpoint::STATUS_PENDING,
                    'sequence' => 5,
                    'timestamp' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            CargoCheckpoint::insert($checkpoints);
        }
    }
}
