<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CargoTrackingMapController extends Controller
{
    private const AREA_COORDINATES = [
        'Arusha' => [-3.3869, 36.6830],
        'Dar es Salaam' => [-6.7924, 39.2083],
        'Dodoma' => [-6.1630, 35.7516],
        'Geita' => [-2.8725, 32.2320],
        'Iringa' => [-7.7708, 35.6923],
        'Kagera' => [-1.9403, 31.1820],
        'Katavi' => [-6.3670, 31.0409],
        'Kigoma' => [-4.8824, 29.6615],
        'Kilimanjaro' => [-3.0674, 37.3556],
        'Lindi' => [-9.9969, 39.7144],
        'Manyara' => [-4.3150, 36.9541],
        'Mara' => [-1.7754, 34.1532],
        'Mbeya' => [-8.9094, 33.4608],
        'Morogoro' => [-6.8278, 37.6591],
        'Mtwara' => [-10.2676, 40.1833],
        'Mwanza' => [-2.5164, 32.9175],
        'Njombe' => [-9.3492, 34.7718],
        'Pemba' => [-5.2050, 39.7756],
        'Pwani' => [-7.3238, 38.8205],
        'Rukwa' => [-7.9667, 31.6167],
        'Ruvuma' => [-10.6879, 35.6501],
        'Shinyanga' => [-3.6619, 33.4231],
        'Simiyu' => [-2.8309, 34.1532],
        'Singida' => [-4.8163, 34.7436],
        'Songwe' => [-8.2726, 32.9308],
        'Tabora' => [-5.0342, 32.8084],
        'Tanga' => [-5.0889, 39.1023],
        'Zanzibar' => [-6.1659, 39.2026],
    ];

    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->role !== 'customer') {
            abort(403, 'Only customers can open cargo map tracking.');
        }

        $cargoes = Cargo::query()
            ->with(['detail', 'transportStaff.user', 'locationUpdates'])
            ->where('customer_id', $user->id)
            ->latest()
            ->get();

        $selectedCargo = $cargoes->firstWhere('id', (int) $request->query('cargo_id'))
            ?? $cargoes->first(fn (Cargo $cargo) => $cargo->status !== Cargo::STATUS_DELIVERED)
            ?? $cargoes->first();

        return view('customer.cargo.tracking-map', [
            'cargoes' => $cargoes,
            'selectedCargo' => $selectedCargo,
            'mapPayload' => $selectedCargo ? $this->mapPayload($selectedCargo) : null,
        ]);
    }

    public function location(Request $request, Cargo $cargo): JsonResponse
    {
        if ($request->user()->role !== 'customer' || (int) $cargo->customer_id !== (int) $request->user()->id) {
            abort(403, 'You can only track your own cargo.');
        }

        return response()->json($this->mapPayload($cargo));
    }

    private function mapPayload(Cargo $cargo): array
    {
        $origin = self::AREA_COORDINATES[$cargo->origin_city] ?? [-6.3690, 34.8888];
        $destination = self::AREA_COORDINATES[$cargo->destination_city] ?? [-6.3690, 34.8888];
        $current = $this->currentCoordinates($cargo, $origin, $destination);
        $movementPoints = $cargo->locationUpdates()
            ->orderBy('recorded_at')
            ->get()
            ->map(fn ($update) => [
                'locationName' => $update->location_name ?: 'Live GPS location',
                'latitude' => (float) $update->latitude,
                'longitude' => (float) $update->longitude,
                'point' => [(float) $update->latitude, (float) $update->longitude],
                'source' => $update->source,
                'recordedAt' => optional($update->recorded_at)->format('d M Y H:i'),
            ])
            ->values();

        return [
            'cargoId' => $cargo->id,
            'trackingNumber' => $cargo->tracking_number,
            'status' => $cargo->statusLabel(),
            'currentLocationLabel' => $this->currentLocationLabel($cargo),
            'currentLocationTime' => optional($cargo->current_location_updated_at)->format('d M Y H:i'),
            'locationUrl' => route('dashboard.cargo-map.location', $cargo),
            'originLabel' => $cargo->origin_city,
            'destinationLabel' => $cargo->destination_city,
            'origin' => $origin,
            'destination' => $destination,
            'current' => $current,
            'route' => [$origin, $destination],
            'movementPoints' => $movementPoints,
            'movementTrail' => $movementPoints->pluck('point')->values(),
        ];
    }

    private function statusProgress(string $status): float
    {
        return match ($status) {
            Cargo::STATUS_PENDING => 0.03,
            Cargo::STATUS_APPROVED => 0.12,
            Cargo::STATUS_IN_TRANSIT => 0.55,
            Cargo::STATUS_ARRIVED => 0.92,
            Cargo::STATUS_DELIVERED => 1.0,
            default => 0.0,
        };
    }

    private function currentLocationLabel(Cargo $cargo): string
    {
        if ($cargo->current_location_city) {
            return $cargo->current_location_city;
        }

        if ($cargo->status === Cargo::STATUS_IN_TRANSIT && $cargo->current_location_lat && $cargo->current_location_lng) {
            return 'Live GPS location';
        }

        return match ($cargo->status) {
            Cargo::STATUS_PENDING, Cargo::STATUS_APPROVED => $cargo->origin_city,
            Cargo::STATUS_IN_TRANSIT => "{$cargo->origin_city} to {$cargo->destination_city}",
            Cargo::STATUS_ARRIVED, Cargo::STATUS_DELIVERED => $cargo->destination_city,
            default => $cargo->origin_city,
        };
    }

    private function currentCoordinates(Cargo $cargo, array $origin, array $destination): array
    {
        if ($cargo->current_location_lat && $cargo->current_location_lng) {
            return [
                (float) $cargo->current_location_lat,
                (float) $cargo->current_location_lng,
            ];
        }

        $progress = $this->statusProgress($cargo->status);

        return [
            $origin[0] + (($destination[0] - $origin[0]) * $progress),
            $origin[1] + (($destination[1] - $origin[1]) * $progress),
        ];
    }
}
