<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CargoTrackingController extends Controller
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
        $trackingNumber = strtoupper(trim((string) $request->query('tracking_number', '')));
        $cargo = null;
        $trackingPayload = null;
        $searched = $trackingNumber !== '';

        if ($searched) {
            $cargo = Cargo::query()
                ->with([
                    'customer',
                    'detail',
                    'transportStaff.user',
                    'locationUpdates' => fn ($query) => $query
                        ->select(['id', 'cargo_id', 'location_name', 'latitude', 'longitude', 'recorded_at'])
                        ->orderBy('recorded_at')
                        ->orderBy('id'),
                ])
                ->where('tracking_number', $trackingNumber)
                ->first();

            if ($cargo) {
                $trackingPayload = $this->trackingPayload($cargo);
            }
        }

        return view('tracking.show', [
            'trackingNumber' => $trackingNumber,
            'cargo' => $cargo,
            'trackingPayload' => $trackingPayload,
            'searched' => $searched,
        ]);
    }

    private function trackingPayload(Cargo $cargo): array
    {
        $origin = $this->areaCoordinates($cargo->origin_city);
        $destination = $this->areaCoordinates($cargo->destination_city);
        $routeCoordinates = $this->routeCoordinates($cargo, $origin, $destination);
        $current = $routeCoordinates[0];
        $transportUser = $cargo->transportStaff?->user;

        return [
            'cargoId' => $cargo->tracking_number,
            'status' => $cargo->statusLabel(),
            'statusClass' => $cargo->statusBadgeClass(),
            'currentLocation' => $cargo->current_location_city ?: $cargo->origin_city,
            'origin' => $cargo->origin_city,
            'destination' => $cargo->destination_city,
            'transporter' => $transportUser?->full_name ?: $transportUser?->name ?: 'Not assigned yet',
            'routeCoordinates' => $routeCoordinates,
            'routeLocations' => $this->routeLocations($cargo, count($routeCoordinates)),
            'currentLatitude' => number_format((float) $current[0], 7, '.', ''),
            'currentLongitude' => number_format((float) $current[1], 7, '.', ''),
            'animationIntervalMs' => 3000,
        ];
    }

    private function routeLocations(Cargo $cargo, int $routeLength): array
    {
        $updates = $cargo->locationUpdates
            ->map(fn ($update) => $update->location_name)
            ->filter()
            ->values();

        $locations = array_fill(0, $routeLength, "{$cargo->origin_city} to {$cargo->destination_city}");
        if ($routeLength > 0) {
            $locations[0] = $cargo->origin_city;
            $locations[$routeLength - 1] = $cargo->destination_city;
        }

        foreach ($updates as $index => $locationName) {
            $routeIndex = min($index + 1, max(0, $routeLength - 2));
            $locations[$routeIndex] = $locationName;
        }

        return $locations;
    }

    private function areaCoordinates(?string $city): array
    {
        return self::AREA_COORDINATES[$city] ?? [-6.3690, 34.8888];
    }

    private function routeCoordinates(Cargo $cargo, array $origin, array $destination): array
    {
        $storedCoordinates = $cargo->locationUpdates
            ->map(fn ($update) => [
                (float) $update->latitude,
                (float) $update->longitude,
            ])
            ->filter(fn (array $position) => $this->isValidPosition($position))
            ->values()
            ->all();

        $coordinates = $storedCoordinates === []
            ? $this->simulatedRoadRoute($origin, $destination)
            : [$origin, ...$storedCoordinates, $destination];

        return collect($coordinates)
            ->filter(fn (array $position) => $this->isValidPosition($position))
            ->unique(fn (array $position) => number_format($position[0], 7).','.number_format($position[1], 7))
            ->values()
            ->all();
    }

    private function simulatedRoadRoute(array $origin, array $destination): array
    {
        $coordinates = [$origin];

        foreach ([0.18, 0.34, 0.50, 0.66, 0.82] as $index => $progress) {
            $curve = sin($progress * pi()) * 0.22;
            $direction = $index % 2 === 0 ? 1 : -1;

            $coordinates[] = [
                $origin[0] + (($destination[0] - $origin[0]) * $progress) + ($curve * $direction),
                $origin[1] + (($destination[1] - $origin[1]) * $progress) - ($curve * 0.45 * $direction),
            ];
        }

        $coordinates[] = $destination;

        return $coordinates;
    }

    private function isValidPosition(array $position): bool
    {
        return count($position) === 2
            && is_finite((float) $position[0])
            && is_finite((float) $position[1]);
    }
}
