<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\CargoLocationUpdate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CargoTrackingController extends Controller
{
    protected \App\Services\GeoService $geoService;

    public function __construct(\App\Services\GeoService $geoService)
    {
        $this->geoService = $geoService;
    }

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
                        ->latest('recorded_at')
                        ->latest('id')
                        ->limit(1),
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

    public function location(string $trackingNumber): JsonResponse
    {
        $cargo = Cargo::query()
            ->with([
                'transportStaff.user',
                'locationUpdates' => fn ($query) => $query
                    ->select(['id', 'cargo_id', 'location_name', 'latitude', 'longitude', 'recorded_at'])
                    ->latest('recorded_at')
                    ->latest('id')
                    ->limit(1),
            ])
            ->where('tracking_number', strtoupper(trim($trackingNumber)))
            ->firstOrFail();

        return response()->json($this->trackingPayload($cargo));
    }

    private function trackingPayload(Cargo $cargo): array
    {
        $origin = $this->areaCoordinates($cargo->origin_city);
        $destination = $this->areaCoordinates($cargo->destination_city);
        $latestUpdate = $cargo->locationUpdates->first();
        $current = $this->currentCoordinates($cargo, $latestUpdate, $origin);
        $transportUser = $cargo->transportStaff?->user;

        $distanceCoveredKm = 0.0;
        $distanceRemainingKm = 0.0;
        $progressPercent = 0;
        $etaFormatted = 'Calculating...';

        if ($this->isValidPosition($current)) {
            $progressData = $this->geoService->getRouteProgress(
                $origin,
                $destination,
                (float) $current[0],
                (float) $current[1]
            );
            $distanceCoveredKm = $progressData['distanceCoveredKm'];
            $distanceRemainingKm = $progressData['distanceRemainingKm'];
            $progressPercent = $progressData['progressPercent'];
            $etaFormatted = $progressData['etaFormatted'];
        }

        // Dynamically reverse geocode if current location city is missing
        $currentLocation = $latestUpdate?->location_name ?: $cargo->current_location_city;
        if (!$currentLocation && $this->isValidPosition($current)) {
            $currentLocation = $this->geoService->reverseGeocode((float)$current[0], (float)$current[1]);
        }
        if (!$currentLocation) {
            $currentLocation = $cargo->origin_city;
        }

        return [
            'cargoId' => $cargo->tracking_number,
            'rawStatus' => $cargo->status,
            'status' => $cargo->statusLabel(),
            'statusClass' => $cargo->statusBadgeClass(),
            'currentLocation' => $currentLocation,
            'origin' => $cargo->origin_city,
            'originCoordinates' => $origin,
            'destination' => $cargo->destination_city,
            'destinationCoordinates' => $destination,
            'transporter' => $transportUser?->full_name ?: $transportUser?->name ?: 'Not assigned yet',
            'currentLatitude' => number_format((float) $current[0], 7, '.', ''),
            'currentLongitude' => number_format((float) $current[1], 7, '.', ''),
            'currentLocationUpdatedAt' => optional($cargo->current_location_updated_at)->toIso8601String(),
            'latestLocationUpdate' => $latestUpdate ? [
                'latitude' => number_format((float) $latestUpdate->latitude, 7, '.', ''),
                'longitude' => number_format((float) $latestUpdate->longitude, 7, '.', ''),
                'locationName' => $latestUpdate->location_name,
                'recordedAt' => optional($latestUpdate->recorded_at)->toIso8601String(),
            ] : null,
            'locationUrl' => route('tracking.location', ['trackingNumber' => $cargo->tracking_number]),
            'distanceCoveredKm' => $distanceCoveredKm,
            'distanceRemainingKm' => $distanceRemainingKm,
            'progressPercent' => $progressPercent,
            'etaFormatted' => $etaFormatted,
        ];
    }

    private function areaCoordinates(?string $city): array
    {
        return self::AREA_COORDINATES[$city] ?? [-6.3690, 34.8888];
    }

    private function currentCoordinates(Cargo $cargo, ?CargoLocationUpdate $latestUpdate, array $origin): array
    {
        if ($latestUpdate && $this->isValidPosition([(float) $latestUpdate->latitude, (float) $latestUpdate->longitude])) {
            return [(float) $latestUpdate->latitude, (float) $latestUpdate->longitude];
        }

        $current = [(float) $cargo->current_location_lat, (float) $cargo->current_location_lng];

        return $this->isValidPosition($current) ? $current : $origin;
    }

    private function isValidPosition(array $position): bool
    {
        return count($position) === 2
            && is_finite((float) $position[0])
            && is_finite((float) $position[1]);
    }
}
