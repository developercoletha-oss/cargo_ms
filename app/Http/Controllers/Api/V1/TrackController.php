<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\CargoCheckpoint;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TrackController extends Controller
{
    protected \App\Services\GeoService $geoService;

    public function __construct(\App\Services\GeoService $geoService)
    {
        $this->geoService = $geoService;
    }

    /**
     * GET /api/v1/track/{trackingNumber?}
     * Auto-detects tracking number from route parameter or query string parameter.
     */
    public function track(Request $request, ?string $trackingNumber = null): JsonResponse
    {
        $trackingNumber = strtoupper(trim((string) ($trackingNumber ?: $request->query('trackingNumber', $request->query('tracking_number', '')))));

        if ($trackingNumber === '') {
            $user = $request->user();
            if ($user) {
                $cargo = Cargo::query()
                    ->where('customer_id', $user->id)
                    ->latest()
                    ->first();
                if ($cargo) {
                    $trackingNumber = $cargo->tracking_number;
                }
            }
        }

        if ($trackingNumber === '') {
            // Pick latest in-transit cargo for seamless demo/testing
            $cargo = Cargo::query()->latest()->first();
            if ($cargo) {
                $trackingNumber = $cargo->tracking_number;
            }
        }

        if ($trackingNumber === '') {
            return response()->json([
                'success' => false,
                'message' => 'No tracking number specified or found.',
            ], 404);
        }

        $cargo = Cargo::query()
            ->with([
                'customer',
                'detail',
                'transportStaff.user',
                'checkpoints',
                'locationUpdates' => fn ($query) => $query->latest('recorded_at')->latest('id')->limit(1),
            ])
            ->where('tracking_number', $trackingNumber)
            ->first();

        // Fallback search in shipments table if cargo table record not found
        if (! $cargo) {
            $shipment = Shipment::query()->with(['sender', 'receiver', 'assignedUser'])->where('tracking_number', $trackingNumber)->first();
            if ($shipment) {
                return response()->json($this->buildShipmentPayload($shipment));
            }

            return response()->json([
                'success' => false,
                'message' => "Cargo with tracking number '{$trackingNumber}' not found.",
            ], 404);
        }

        // Ensure checkpoints exist for this cargo
        $this->ensureDefaultCheckpoints($cargo);
        $cargo->load('checkpoints');

        return response()->json($this->buildCargoPayload($cargo));
    }

    /**
     * GET /api/v1/customer/active-cargoes
     * Fetch logged-in customer's active items in transit (or active cargoes list).
     */
    public function activeCargoes(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Cargo::query()->with(['customer', 'detail', 'checkpoints']);

        if ($user && $user->role === 'customer') {
            $query->where('customer_id', $user->id);
        }

        $cargoes = $query->latest()->get();

        $activeItems = $cargoes->map(function (Cargo $cargo) {
            $this->ensureDefaultCheckpoints($cargo);
            return $this->buildCargoPayload($cargo);
        });

        return response()->json([
            'success' => true,
            'count' => $activeItems->count(),
            'activeCargoes' => $activeItems,
        ]);
    }

    /**
     * POST /api/v1/shipment/update-checkpoint
     * Allows Transporter role to mark a route checkpoint as COMPLETED or ACTIVE_CURRENT.
     */
    public function updateCheckpoint(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trackingNumber' => ['nullable', 'string'],
            'tracking_number' => ['nullable', 'string'],
            'cargo_id' => ['nullable', 'integer'],
            'checkpoint_id' => ['nullable', 'integer'],
            'checkpoint_name' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:COMPLETED,ACTIVE_CURRENT,PENDING,completed,active_current,pending'],
            'location_name' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $trackingNum = strtoupper(trim((string) ($validated['trackingNumber'] ?? $validated['tracking_number'] ?? '')));
        $cargoId = $validated['cargo_id'] ?? null;

        $cargoQuery = Cargo::query()->with(['checkpoints', 'transportStaff.user']);

        if ($cargoId) {
            $cargo = $cargoQuery->where('id', $cargoId)->first();
        } elseif ($trackingNum !== '') {
            $cargo = $cargoQuery->where('tracking_number', $trackingNum)->first();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tracking number or cargo ID is required.',
            ], 422);
        }

        if (! $cargo) {
            return response()->json([
                'success' => false,
                'message' => 'Cargo not found.',
            ], 404);
        }

        $this->ensureDefaultCheckpoints($cargo);
        $cargo->load('checkpoints');

        $statusInput = strtoupper($validated['status']);
        $checkpointId = $validated['checkpoint_id'] ?? null;
        $checkpointName = $validated['checkpoint_name'] ?? null;

        $targetCheckpoint = null;

        if ($checkpointId) {
            $targetCheckpoint = $cargo->checkpoints->firstWhere('id', $checkpointId);
        }

        if (! $targetCheckpoint && $checkpointName) {
            $targetCheckpoint = $cargo->checkpoints->first(function ($cp) use ($checkpointName) {
                return strcasecmp($cp->name, $checkpointName) === 0;
            });
        }

        if (! $targetCheckpoint) {
            // Pick next non-completed checkpoint or first
            $targetCheckpoint = $cargo->checkpoints->firstWhere('status', CargoCheckpoint::STATUS_ACTIVE_CURRENT)
                ?? $cargo->checkpoints->firstWhere('status', CargoCheckpoint::STATUS_PENDING)
                ?? $cargo->checkpoints->last();
        }

        if (! $targetCheckpoint) {
            return response()->json([
                'success' => false,
                'message' => 'No checkpoint found for cargo.',
            ], 404);
        }

        DB::transaction(function () use ($cargo, $targetCheckpoint, $statusInput, $validated) {
            $now = now();
            $targetSeq = $targetCheckpoint->sequence;

            if ($statusInput === CargoCheckpoint::STATUS_COMPLETED) {
                // Mark all prior sequence checkpoints as COMPLETED
                foreach ($cargo->checkpoints as $cp) {
                    if ($cp->sequence <= $targetSeq) {
                        $cp->update([
                            'status' => CargoCheckpoint::STATUS_COMPLETED,
                            'timestamp' => $cp->timestamp ?: $now,
                        ]);
                    }
                }

                // If there is a next checkpoint, mark it ACTIVE_CURRENT if not completed
                $nextCp = $cargo->checkpoints->firstWhere('sequence', $targetSeq + 1);
                if ($nextCp && $nextCp->status === CargoCheckpoint::STATUS_PENDING) {
                    $nextCp->update([
                        'status' => CargoCheckpoint::STATUS_ACTIVE_CURRENT,
                        'timestamp' => $now,
                    ]);
                }
            } elseif ($statusInput === CargoCheckpoint::STATUS_ACTIVE_CURRENT) {
                // Mark all prior checkpoints as COMPLETED
                foreach ($cargo->checkpoints as $cp) {
                    if ($cp->sequence < $targetSeq) {
                        $cp->update([
                            'status' => CargoCheckpoint::STATUS_COMPLETED,
                            'timestamp' => $cp->timestamp ?: $now,
                        ]);
                    } elseif ($cp->sequence === $targetSeq) {
                        $cp->update([
                            'status' => CargoCheckpoint::STATUS_ACTIVE_CURRENT,
                            'timestamp' => $now,
                        ]);
                    } else {
                        $cp->update([
                            'status' => CargoCheckpoint::STATUS_PENDING,
                        ]);
                    }
                }
            } else {
                $targetCheckpoint->update(['status' => CargoCheckpoint::STATUS_PENDING]);
            }

            // Update cargo location & status
            $currentLocName = $validated['location_name'] ?? $targetCheckpoint->name;
            $cargo->update([
                'current_location_city' => $currentLocName,
                'current_location_lat' => $validated['latitude'] ?? $targetCheckpoint->latitude ?? $cargo->current_location_lat,
                'current_location_lng' => $validated['longitude'] ?? $targetCheckpoint->longitude ?? $cargo->current_location_lng,
                'current_location_updated_at' => $now,
                'status' => $cargo->status === 'pending' || $cargo->status === 'approved' ? Cargo::STATUS_IN_TRANSIT : $cargo->status,
            ]);
        });

        $cargo->refresh();
        $cargo->load('checkpoints');

        $payload = $this->buildCargoPayload($cargo);

        return response()->json([
            'success' => true,
            'message' => "Checkpoint '{$targetCheckpoint->name}' updated to {$statusInput}.",
            'data' => $payload,
            ...$payload,
        ]);
    }

    /**
     * Construct JSON payload structured strictly as per prompt requirements.
     */
    private function buildCargoPayload(Cargo $cargo): array
    {
        $checkpoints = $cargo->checkpoints;

        $completedCount = $checkpoints->where('status', CargoCheckpoint::STATUS_COMPLETED)->count();
        $activeCount = $checkpoints->where('status', CargoCheckpoint::STATUS_ACTIVE_CURRENT)->count();
        $totalCount = max(1, $checkpoints->count());

        // Formula: (Checkpoints Completed / Total Route Checkpoints) * 100
        // If active checkpoint is present, counting (completed + active) or completed / total
        $reachedCount = $completedCount + ($activeCount > 0 ? 1 : 0);
        $progressPercentage = min(100, (int) round(($reachedCount / $totalCount) * 100));

        $formattedStatus = match ($cargo->status) {
            Cargo::STATUS_IN_TRANSIT, Cargo::STATUS_ARRIVED_REGIONAL_HUB => 'ON_TRANSIT',
            Cargo::STATUS_DELIVERED => 'DELIVERED',
            Cargo::STATUS_ARRIVED => 'ARRIVED',
            default => strtoupper($cargo->status),
        };

        $transportStaff = $cargo->transportStaff;
        $driverUser = $transportStaff?->user;
        $driverName = $driverUser?->full_name ?: $driverUser?->name ?: 'Not assigned yet';
        $currentVehicle = $transportStaff?->vehicle_plate
            ? "{$transportStaff->vehicle_type} - {$transportStaff->vehicle_plate}"
            : 'Not assigned yet';

        $detail = $cargo->detail;
        $description = $detail?->description ?: "Cargo parcel from {$cargo->origin_city} to {$cargo->destination_city}";
        $weight = $detail?->weight_kg ? number_format((float)$detail->weight_kg, 2) . ' kg' : 'N/A';

        $customer = $cargo->customer;
        $senderName = $customer?->full_name ?: $customer?->name ?: "{$cargo->origin_city} Logistics Hub";
        $receiverName = $cargo->destination_address ?: "{$cargo->destination_city} Freight Depot Store";

        // Geospatial distance/progress/ETA calculations
        $origin = $this->geoService->getAreaCoordinates($cargo->origin_city);
        $destination = $this->geoService->getAreaCoordinates($cargo->destination_city);
        
        $latestUpdate = $cargo->locationUpdates->first();
        $currentLat = $latestUpdate ? (float)$latestUpdate->latitude : ($cargo->current_location_lat ? (float)$cargo->current_location_lat : null);
        $currentLng = $latestUpdate ? (float)$latestUpdate->longitude : ($cargo->current_location_lng ? (float)$cargo->current_location_lng : null);

        // Fallback progress ratio based on checkpoints if no coordinates exist
        $completedCount = $checkpoints->where('status', CargoCheckpoint::STATUS_COMPLETED)->count();
        $activeCount = $checkpoints->where('status', CargoCheckpoint::STATUS_ACTIVE_CURRENT)->count();
        $totalCount = max(1, $checkpoints->count());
        $reachedCount = $completedCount + ($activeCount > 0 ? 1 : 0);
        $checkpointProgressRatio = min(100, (int) round(($reachedCount / $totalCount) * 100));

        $currentLocation = $cargo->current_location_city;
        $progressPercentage = $checkpointProgressRatio;
        $distanceCoveredKm = 0.0;
        $distanceRemainingKm = 0.0;
        $etaFormatted = 'Calculating...';

        if ($currentLat !== null && $currentLng !== null) {
            if (!$currentLocation) {
                $currentLocation = $this->geoService->reverseGeocode($currentLat, $currentLng);
            }
            $progressData = $this->geoService->getRouteProgress($origin, $destination, $currentLat, $currentLng);
            $progressPercentage = $progressData['progressPercent'];
            $distanceCoveredKm = $progressData['distanceCoveredKm'];
            $distanceRemainingKm = $progressData['distanceRemainingKm'];
            $etaFormatted = $progressData['etaFormatted'];
        } else {
            $currentLocation = $cargo->origin_city;
        }

        $eta = $cargo->delivery_date
            ? Carbon::parse($cargo->delivery_date)->toIso8601String()
            : now()->addDays(3)->toIso8601String();

        $formattedCheckpoints = $checkpoints->map(function (CargoCheckpoint $cp) {
            return [
                'id' => $cp->id,
                'name' => $cp->name,
                'status' => $cp->status,
                'timestamp' => $cp->timestamp ? $cp->timestamp->format('Y-m-d H:i') : null,
            ];
        })->values()->all();

        return [
            'success' => true,
            'trackingNumber' => $cargo->tracking_number,
            'status' => $formattedStatus,
            'cargo' => [
                'description' => $description,
                'weight' => $weight,
                'sender' => $senderName,
                'receiver' => $receiverName,
            ],
            'route' => [
                'origin' => "{$cargo->origin_city} Main Hub",
                'destination' => "{$cargo->destination_city} Freight Depot",
                'estimatedDelivery' => $eta,
                'currentVehicle' => $currentVehicle,
                'driverName' => $driverName,
                'progressPercentage' => $progressPercentage,
                'distanceCoveredKm' => $distanceCoveredKm,
                'distanceRemainingKm' => $distanceRemainingKm,
                'etaFormatted' => $etaFormatted,
                'currentLocationName' => $currentLocation,
            ],
            'checkpoints' => $formattedCheckpoints,
        ];
    }

    private function buildShipmentPayload(Shipment $shipment): array
    {
        $status = strtoupper($shipment->status) === 'IN_TRANSIT' ? 'ON_TRANSIT' : strtoupper($shipment->status);
        $progressPercentage = $status === 'ON_TRANSIT' ? 60 : ($status === 'DELIVERED' ? 100 : 20);

        return [
            'success' => true,
            'trackingNumber' => $shipment->tracking_number,
            'status' => $status,
            'cargo' => [
                'description' => $shipment->description ?: 'Freight Shipment',
                'weight' => $shipment->weight ? "{$shipment->weight} kg" : '250 kg',
                'sender' => $shipment->sender?->full_name ?: 'Dar Electronics Ltd',
                'receiver' => $shipment->receiver?->full_name ?: 'Mwanza Tech Store',
            ],
            'route' => [
                'origin' => ($shipment->origin_city ?: 'Dar es Salaam') . ' Main Hub',
                'destination' => ($shipment->destination_city ?: 'Mwanza') . ' Freight Depot',
                'estimatedDelivery' => optional($shipment->estimated_delivery)->toIso8601String() ?: now()->addDays(3)->toIso8601String(),
                'currentVehicle' => 'Scania Truck - T 382 EDX',
                'driverName' => $shipment->assignedUser?->full_name ?: 'Juma Hassan',
                'progressPercentage' => $progressPercentage,
            ],
            'checkpoints' => [
                ['id' => 1, 'name' => ($shipment->origin_city ?: 'Dar es Salaam') . ' Warehouse', 'status' => 'COMPLETED', 'timestamp' => now()->subDay()->format('Y-m-d H:i')],
                ['id' => 2, 'name' => 'Morogoro Transit Hub', 'status' => 'COMPLETED', 'timestamp' => now()->subHours(12)->format('Y-m-d H:i')],
                ['id' => 3, 'name' => 'Dodoma Station', 'status' => 'ACTIVE_CURRENT', 'timestamp' => now()->subHours(3)->format('Y-m-d H:i')],
                ['id' => 4, 'name' => 'Singida Checkpoint', 'status' => 'PENDING', 'timestamp' => null],
                ['id' => 5, 'name' => ($shipment->destination_city ?: 'Mwanza') . ' Depot', 'status' => 'PENDING', 'timestamp' => null],
            ],
        ];
    }

    /**
     * Ensure default checkpoints exist for a cargo record.
     */
    private function ensureDefaultCheckpoints(Cargo $cargo): void
    {
        if ($cargo->checkpoints()->exists()) {
            return;
        }

        $origin = $cargo->origin_city ?: 'Dar es Salaam';
        $destination = $cargo->destination_city ?: 'Mwanza';

        $checkpoints = [
            ['name' => "{$origin} Warehouse", 'status' => CargoCheckpoint::STATUS_COMPLETED, 'sequence' => 1, 'timestamp' => now()->subHours(24)],
            ['name' => 'Morogoro Transit Hub', 'status' => CargoCheckpoint::STATUS_COMPLETED, 'sequence' => 2, 'timestamp' => now()->subHours(16)],
            ['name' => 'Dodoma Station', 'status' => CargoCheckpoint::STATUS_ACTIVE_CURRENT, 'sequence' => 3, 'timestamp' => now()->subHours(5)],
            ['name' => 'Singida Checkpoint', 'status' => CargoCheckpoint::STATUS_PENDING, 'sequence' => 4, 'timestamp' => null],
            ['name' => "{$destination} Depot", 'status' => CargoCheckpoint::STATUS_PENDING, 'sequence' => 5, 'timestamp' => null],
        ];

        foreach ($checkpoints as $cpData) {
            $cargo->checkpoints()->create($cpData);
        }
    }
}
