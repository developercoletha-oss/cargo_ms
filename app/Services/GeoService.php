<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Centralized Geospatial Service for CFTMS Live Transit Tracking.
 *
 * Provides:
 *  - Nominatim reverse geocoding (real location names)
 *  - OSRM road-network routing (real route geometry)
 *  - Haversine distance formula
 *  - Route progress & ETA calculation
 *  - Snap-to-route positioning
 */
class GeoService
{
    /** Earth's mean radius in meters. */
    private const EARTH_RADIUS_M = 6_371_000;

    /** Default average truck speed in km/h for ETA estimation. */
    private const DEFAULT_SPEED_KMH = 60;

    /** Nominatim reverse-geocoding endpoint. */
    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/reverse';

    /** OSRM routing endpoint. */
    private const OSRM_URL = 'https://router.project-osrm.org/route/v1/driving';

    /** User-Agent header for Nominatim (required by their usage policy). */
    private const USER_AGENT = 'CFTMS/1.0 (Cargo Freight Tracking Management System)';

    /** Cache TTL for geocoding results (24 hours). */
    private const GEOCODE_CACHE_TTL = 86400;

    /** Cache TTL for OSRM route results (6 hours). */
    private const ROUTE_CACHE_TTL = 21600;

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

    public function getAreaCoordinates(?string $city): array
    {
        return self::AREA_COORDINATES[$city] ?? [-6.3690, 34.8888];
    }

    // ------------------------------------------------------------------
    //  Nominatim Reverse Geocoding
    // ------------------------------------------------------------------

    /**
     * Resolve latitude/longitude to a human-readable location name
     * using the OpenStreetMap Nominatim reverse geocoding API.
     *
     * Returns e.g. "Chalinze, Pwani" or "Same, Kilimanjaro".
     * Falls back to a coordinate string on API failure.
     */
    public function reverseGeocode(float $lat, float $lng): string
    {
        $cacheKey = "geo:nominatim:" . round($lat, 4) . ',' . round($lng, 4);

        return Cache::remember($cacheKey, self::GEOCODE_CACHE_TTL, function () use ($lat, $lng) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => self::USER_AGENT,
                    'Accept-Language' => 'en',
                ])
                    ->timeout(8)
                    ->get(self::NOMINATIM_URL, [
                        'format' => 'json',
                        'lat' => $lat,
                        'lon' => $lng,
                        'zoom' => 14,
                        'addressdetails' => 1,
                    ]);

                if (! $response->successful()) {
                    Log::warning('GeoService: Nominatim HTTP error', [
                        'status' => $response->status(),
                        'lat' => $lat,
                        'lng' => $lng,
                    ]);

                    return $this->coordinateFallback($lat, $lng);
                }

                $data = $response->json();

                return $this->formatNominatimAddress($data['address'] ?? [])
                    ?: $this->coordinateFallback($lat, $lng);
            } catch (\Throwable $e) {
                Log::warning('GeoService: Nominatim request failed', [
                    'error' => $e->getMessage(),
                    'lat' => $lat,
                    'lng' => $lng,
                ]);

                return $this->coordinateFallback($lat, $lng);
            }
        });
    }

    /**
     * Build a compact, human-readable location string from a Nominatim address object.
     *
     * Priority: town/city/village + state/county.
     * Examples: "Chalinze, Pwani", "Same, Kilimanjaro", "Dodoma, Dodoma".
     */
    private function formatNominatimAddress(array $address): string
    {
        $locality = $address['town']
            ?? $address['city']
            ?? $address['village']
            ?? $address['suburb']
            ?? $address['hamlet']
            ?? $address['municipality']
            ?? null;

        $region = $address['state']
            ?? $address['county']
            ?? $address['state_district']
            ?? null;

        if ($locality && $region && strtolower($locality) !== strtolower($region)) {
            return "{$locality}, {$region}";
        }

        if ($locality) {
            return $locality;
        }

        if ($region) {
            return $region;
        }

        $country = $address['country'] ?? null;

        return $country ?: '';
    }

    /**
     * Fallback display when geocoding fails — returns a formatted coordinate string.
     */
    private function coordinateFallback(float $lat, float $lng): string
    {
        return number_format($lat, 4) . ', ' . number_format($lng, 4);
    }

    // ------------------------------------------------------------------
    //  OSRM Road-Network Routing
    // ------------------------------------------------------------------

    /**
     * Fetch real driving-route geometry from the OSRM routing engine.
     *
     * Returns an array with:
     *   - 'distance'  => total route distance in meters
     *   - 'duration'  => estimated driving time in seconds
     *   - 'geometry'  => array of [lat, lng] coordinate pairs along the road
     *
     * Returns null on API failure (caller should use existing Leaflet Routing Machine fallback).
     */
    public function fetchOsrmRoute(array $origin, array $destination): ?array
    {
        $cacheKey = 'geo:osrm:'
            . round($origin[0], 4) . ',' . round($origin[1], 4)
            . '_'
            . round($destination[0], 4) . ',' . round($destination[1], 4);

        return Cache::remember($cacheKey, self::ROUTE_CACHE_TTL, function () use ($origin, $destination) {
            try {
                // OSRM expects lng,lat order in the URL
                $url = self::OSRM_URL
                    . '/' . $origin[1] . ',' . $origin[0]
                    . ';' . $destination[1] . ',' . $destination[0]
                    . '?overview=full&geometries=geojson';

                $response = Http::withHeaders([
                    'User-Agent' => self::USER_AGENT,
                ])
                    ->timeout(10)
                    ->get($url);

                if (! $response->successful()) {
                    Log::warning('GeoService: OSRM HTTP error', [
                        'status' => $response->status(),
                        'origin' => $origin,
                        'destination' => $destination,
                    ]);

                    return null;
                }

                $data = $response->json();

                if (($data['code'] ?? '') !== 'Ok' || empty($data['routes'])) {
                    return null;
                }

                $route = $data['routes'][0];
                $geoJsonCoords = $route['geometry']['coordinates'] ?? [];

                // GeoJSON coordinates are [lng, lat] — convert to [lat, lng]
                $geometry = array_map(
                    fn(array $coord) => [$coord[1], $coord[0]],
                    $geoJsonCoords
                );

                return [
                    'distance' => (float) ($route['distance'] ?? 0),
                    'duration' => (float) ($route['duration'] ?? 0),
                    'geometry' => $geometry,
                ];
            } catch (\Throwable $e) {
                Log::warning('GeoService: OSRM request failed', [
                    'error' => $e->getMessage(),
                    'origin' => $origin,
                    'destination' => $destination,
                ]);

                return null;
            }
        });
    }

    // ------------------------------------------------------------------
    //  Haversine Distance Formula
    // ------------------------------------------------------------------

    /**
     * Calculate the great-circle distance between two points using the Haversine formula.
     *
     * d = 2r * arcsin( sqrt( sin²(Δφ/2) + cos(φ1) * cos(φ2) * sin²(Δλ/2) ) )
     *
     * @return float Distance in meters
     */
    public function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) ** 2;

        return 2 * self::EARTH_RADIUS_M * asin(sqrt($a));
    }

    // ------------------------------------------------------------------
    //  Route Progress & Distance Calculations
    // ------------------------------------------------------------------

    /**
     * Calculate distance travelled along an OSRM route geometry up to a given GPS point.
     *
     * Finds the closest point on the polyline and sums segment distances from the start.
     *
     * @param  array  $routeGeometry  Array of [lat, lng] pairs from OSRM
     * @return float  Distance in meters from route start to the nearest projected point
     */
    public function distanceAlongRoute(array $routeGeometry, float $lat, float $lng): float
    {
        if (count($routeGeometry) < 2) {
            return 0.0;
        }

        $nearestIndex = 0;
        $nearestDistance = PHP_FLOAT_MAX;

        foreach ($routeGeometry as $index => $point) {
            $dist = $this->haversineDistance($lat, $lng, $point[0], $point[1]);

            if ($dist < $nearestDistance) {
                $nearestDistance = $dist;
                $nearestIndex = $index;
            }
        }

        $travelled = 0.0;

        for ($i = 1; $i <= $nearestIndex; $i++) {
            $travelled += $this->haversineDistance(
                $routeGeometry[$i - 1][0],
                $routeGeometry[$i - 1][1],
                $routeGeometry[$i][0],
                $routeGeometry[$i][1]
            );
        }

        return $travelled;
    }

    /**
     * Calculate live progress along a route.
     *
     * @param  array  $routeGeometry   OSRM polyline as [lat, lng] pairs
     * @param  float  $totalDistance    Total route distance in meters (from OSRM)
     * @param  float  $lat             Current GPS latitude
     * @param  float  $lng             Current GPS longitude
     * @param  float|null  $speedKmh   Current vehicle speed (km/h), null = use default
     * @return array{distanceCoveredKm: float, distanceRemainingKm: float, progressPercent: int, etaMinutes: int, etaFormatted: string}
     */
    public function calculateProgress(
        array $routeGeometry,
        float $totalDistance,
        float $lat,
        float $lng,
        ?float $speedKmh = null
    ): array {
        $distanceCovered = $this->distanceAlongRoute($routeGeometry, $lat, $lng);
        $distanceCovered = min($distanceCovered, $totalDistance);
        $distanceRemaining = max(0.0, $totalDistance - $distanceCovered);
        $progressPercent = $totalDistance > 0
            ? (int) min(100, round(($distanceCovered / $totalDistance) * 100))
            : 0;

        $speed = ($speedKmh && $speedKmh > 0) ? $speedKmh : self::DEFAULT_SPEED_KMH;
        $etaMinutes = $speed > 0
            ? (int) ceil(($distanceRemaining / 1000) / $speed * 60)
            : 0;

        return [
            'distanceCoveredKm' => round($distanceCovered / 1000, 2),
            'distanceRemainingKm' => round($distanceRemaining / 1000, 2),
            'progressPercent' => $progressPercent,
            'etaMinutes' => $etaMinutes,
            'etaFormatted' => $this->formatEta($etaMinutes),
        ];
    }

    /**
     * Calculate progress using only Haversine (fallback when no OSRM route available).
     *
     * Uses straight-line distances: origin→current and origin→destination.
     */
    public function calculateProgressHaversine(
        array $origin,
        array $destination,
        float $lat,
        float $lng,
        ?float $speedKmh = null
    ): array {
        $totalDistance = $this->haversineDistance($origin[0], $origin[1], $destination[0], $destination[1]);
        $distanceCovered = $this->haversineDistance($origin[0], $origin[1], $lat, $lng);
        $distanceCovered = min($distanceCovered, $totalDistance);
        $distanceRemaining = max(0.0, $totalDistance - $distanceCovered);
        $progressPercent = $totalDistance > 0
            ? (int) min(100, round(($distanceCovered / $totalDistance) * 100))
            : 0;

        $speed = ($speedKmh && $speedKmh > 0) ? $speedKmh : self::DEFAULT_SPEED_KMH;
        $etaMinutes = $speed > 0
            ? (int) ceil(($distanceRemaining / 1000) / $speed * 60)
            : 0;

        return [
            'distanceCoveredKm' => round($distanceCovered / 1000, 2),
            'distanceRemainingKm' => round($distanceRemaining / 1000, 2),
            'progressPercent' => $progressPercent,
            'etaMinutes' => $etaMinutes,
            'etaFormatted' => $this->formatEta($etaMinutes),
        ];
    }

    /**
     * Snap a GPS coordinate to the nearest point on the OSRM route polyline.
     *
     * @return array [lat, lng] of the nearest route point
     */
    public function snapToRoute(array $routeGeometry, float $lat, float $lng): array
    {
        if (empty($routeGeometry)) {
            return [$lat, $lng];
        }

        $nearestPoint = $routeGeometry[0];
        $nearestDistance = PHP_FLOAT_MAX;

        foreach ($routeGeometry as $point) {
            $dist = $this->haversineDistance($lat, $lng, $point[0], $point[1]);

            if ($dist < $nearestDistance) {
                $nearestDistance = $dist;
                $nearestPoint = $point;
            }
        }

        return $nearestPoint;
    }

    // ------------------------------------------------------------------
    //  Formatting Helpers
    // ------------------------------------------------------------------

    /**
     * Format ETA minutes into a human-readable string.
     *
     * Examples: "45 mins remaining", "3 hours 20 mins remaining", "Arrived"
     */
    public function formatEta(int $minutes): string
    {
        if ($minutes <= 0) {
            return 'Arrived';
        }

        if ($minutes < 60) {
            return "{$minutes} min" . ($minutes !== 1 ? 's' : '') . ' remaining';
        }

        $hours = intdiv($minutes, 60);
        $remainingMins = $minutes % 60;

        $result = "{$hours} hour" . ($hours !== 1 ? 's' : '');

        if ($remainingMins > 0) {
            $result .= " {$remainingMins} min" . ($remainingMins !== 1 ? 's' : '');
        }

        return $result . ' remaining';
    }

    /**
     * Get progress data for a cargo shipment given origin/destination city coordinates and current GPS position.
     *
     * Tries OSRM first, falls back to Haversine if OSRM is unavailable.
     */
    public function getRouteProgress(
        array $originCoords,
        array $destinationCoords,
        float $currentLat,
        float $currentLng,
        ?float $speedKmh = null
    ): array {
        $osrmRoute = $this->fetchOsrmRoute($originCoords, $destinationCoords);

        if ($osrmRoute && ! empty($osrmRoute['geometry'])) {
            return $this->calculateProgress(
                $osrmRoute['geometry'],
                $osrmRoute['distance'],
                $currentLat,
                $currentLng,
                $speedKmh
            );
        }

        // Fallback to Haversine straight-line
        return $this->calculateProgressHaversine(
            $originCoords,
            $destinationCoords,
            $currentLat,
            $currentLng,
            $speedKmh
        );
    }
}
