<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\CargoLocationUpdate;
use App\Services\GeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransporterGpsController extends Controller
{
    protected GeoService $geoService;

    public function __construct(GeoService $geoService)
    {
        $this->geoService = $geoService;
    }

    /**
     * POST /api/v1/transporter/update-gps
     * Receives raw GPS pings from transporters and records geocoded location history.
     */
    public function updateGps(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trackingNumber' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
            'timestamp' => ['nullable', 'date'],
        ]);

        $trackingNumber = strtoupper(trim($validated['trackingNumber']));
        $latitude = (float) $validated['latitude'];
        $longitude = (float) $validated['longitude'];
        $speed = isset($validated['speed']) ? (float) $validated['speed'] : null;
        $heading = isset($validated['heading']) ? (float) $validated['heading'] : null;
        $recordedAt = $validated['timestamp'] ? now()->parse($validated['timestamp']) : now();

        $cargo = Cargo::query()
            ->where('tracking_number', $trackingNumber)
            ->first();

        if (! $cargo) {
            return response()->json([
                'success' => false,
                'message' => "Cargo with tracking number '{$trackingNumber}' not found.",
            ], 404);
        }

        // Call real reverse geocoder to resolve locality
        $locationName = $this->geoService->reverseGeocode($latitude, $longitude);

        // Fetch origin & destination coordinates for distance calculations
        $originCoords = $this->geoService->getRouteProgress(
            $this->areaCoordinates($cargo->origin_city),
            $this->areaCoordinates($cargo->destination_city),
            $latitude,
            $longitude,
            $speed
        );

        DB::transaction(function () use ($cargo, $locationName, $latitude, $longitude, $speed, $heading, $recordedAt) {
            // Store location update history
            CargoLocationUpdate::create([
                'cargo_id' => $cargo->id,
                'reported_by' => auth()->id(),
                'location_name' => $locationName,
                'geocoded_name' => $locationName,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'speed' => $speed,
                'heading' => $heading,
                'source' => 'gps_live',
                'recorded_at' => $recordedAt,
            ]);

            // Update Cargo current location fields
            $cargo->update([
                'current_location_city' => $locationName,
                'current_location_lat' => $latitude,
                'current_location_lng' => $longitude,
                'current_location_updated_at' => $recordedAt,
                'status' => in_array($cargo->status, [Cargo::STATUS_PENDING, Cargo::STATUS_APPROVED])
                    ? Cargo::STATUS_IN_TRANSIT
                    : $cargo->status,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'GPS location update received and geocoded.',
            'geocodedLocation' => $locationName,
            'coordinates' => [
                'lat' => $latitude,
                'lng' => $longitude,
            ],
            'progress' => [
                'distanceCoveredKm' => $originCoords['distanceCoveredKm'],
                'distanceRemainingKm' => $originCoords['distanceRemainingKm'],
                'progressPercent' => $originCoords['progressPercent'],
                'etaMinutes' => $originCoords['etaMinutes'],
                'etaFormatted' => $originCoords['etaFormatted'],
            ],
        ]);
    }

    private function areaCoordinates(?string $city): array
    {
        $coordinates = [
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

        return $coordinates[$city] ?? [-6.3690, 34.8888];
    }
}
