<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\TransportStaff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        $mode = $this->trackingMode($request);
        $cargoes = $this->visibleCargoQuery($request)->latest()->get();
        $selectedCargo = $mode === 'manager'
            ? null
            : $this->selectedCargo($cargoes, (int) $request->query('cargo_id'), $mode);

        return view('customer.cargo.tracking-map', [
            'mode' => $mode,
            'cargoes' => $cargoes,
            'selectedCargo' => $selectedCargo,
            'mapPayload' => $mode === 'manager'
                ? $this->managerPayload($cargoes)
                : ($selectedCargo ? $this->cargoPayload($request, $selectedCargo) : null),
            'overviewUrl' => $mode === 'manager' ? route('dashboard.cargo-map.overview') : null,
        ]);
    }

    public function overview(Request $request): JsonResponse
    {
        if ($this->trackingMode($request) !== 'manager') {
            abort(403, 'Only managers can open the cargo overview map.');
        }

        return response()->json(
            $this->managerPayload($this->visibleCargoQuery($request)->latest()->get())
        );
    }

    public function location(Request $request, Cargo $cargo): JsonResponse
    {
        $cargo = $this->visibleCargoQuery($request)
            ->whereKey($cargo->id)
            ->firstOrFail();

        return response()->json($this->cargoPayload($request, $cargo));
    }

    private function visibleCargoQuery(Request $request): Builder
    {
        $user = $request->user();
        $mode = $this->trackingMode($request);

        $query = Cargo::query()
            ->select([
                'id',
                'tracking_number',
                'customer_id',
                'transport_staff_id',
                'origin_country',
                'origin_city',
                'origin_address',
                'destination_country',
                'destination_city',
                'destination_address',
                'current_location_city',
                'current_location_lat',
                'current_location_lng',
                'current_location_updated_at',
                'pickup_date',
                'delivery_date',
                'status',
                'approval_note',
                'signed_at',
                'handover_confirmed_at',
                'created_at',
                'updated_at',
            ])
            ->with([
                'customer:id,name,full_name,email,phone,company_name',
                'detail:id,cargo_id,description,cargo_type,weight_kg,volume_cbm,quantity,package_count,estimated_value,is_fragile,is_hazardous,special_instructions',
                'locationUpdates' => fn ($query) => $query
                    ->select(['id', 'cargo_id', 'location_name', 'latitude', 'longitude', 'recorded_at'])
                    ->latest('recorded_at')
                    ->latest('id')
                    ->limit(1),
                'transportStaff:id,user_id,staff_code,vehicle_type,vehicle_plate,is_active',
                'transportStaff.user:id,name,full_name,email,phone',
            ])
            ->withCount('locationUpdates');

        if ($mode === 'customer') {
            $query->where('customer_id', $user->id);
        }

        if ($mode === 'transporter') {
            $transportStaffId = TransportStaff::query()
                ->where('user_id', $user->id)
                ->value('id');

            $query->where('transport_staff_id', $transportStaffId ?: 0);
        }

        return $query;
    }

    private function trackingMode(Request $request): string
    {
        return match ($request->user()?->role) {
            'admin', 'manager' => 'manager',
            'transporter' => 'transporter',
            default => 'customer',
        };
    }

    private function selectedCargo(Collection $cargoes, int $cargoId, string $mode): ?Cargo
    {
        if ($cargoId > 0) {
            $selected = $cargoes->firstWhere('id', $cargoId);

            if ($selected) {
                return $selected;
            }
        }

        if ($mode === 'transporter') {
            return $cargoes->first(fn (Cargo $cargo) => $cargo->status === Cargo::STATUS_IN_TRANSIT)
                ?? $cargoes->first(fn (Cargo $cargo) => $cargo->status === Cargo::STATUS_ARRIVED_REGIONAL_HUB)
                ?? $cargoes->first(fn (Cargo $cargo) => $cargo->status === Cargo::STATUS_APPROVED)
                ?? $cargoes->first(fn (Cargo $cargo) => $cargo->status !== Cargo::STATUS_DELIVERED)
                ?? $cargoes->first();
        }

        return $cargoes->first(fn (Cargo $cargo) => $cargo->status !== Cargo::STATUS_DELIVERED)
            ?? $cargoes->first();
    }

    private function managerPayload(Collection $cargoes): array
    {
        $storeMarkers = [];
        $cargoMarkers = [];

        foreach ($cargoes as $cargo) {
            $origin = $this->areaCoordinates($cargo->origin_city);
            $destination = $this->areaCoordinates($cargo->destination_city);
            $current = $this->currentCoordinates($cargo, $origin, $destination);
            $cargoInfo = $this->cargoInfo($cargo, $current);

            $this->appendStoreMarker($storeMarkers, $cargo, 'pickup', 'Pickup Store', $origin);
            $this->appendStoreMarker($storeMarkers, $cargo, 'destination', 'Destination Store', $destination);

            $cargoMarkers[] = [
                'key' => "cargo:{$cargo->id}",
                'entityType' => 'cargo',
                'variant' => 'cargo',
                'iconClass' => 'bi-box-seam',
                'title' => $cargo->tracking_number,
                'subtitle' => $this->currentLocationLabel($cargo),
                'position' => $current,
                'routeWaypoints' => [$origin, $destination],
                'cargo' => $cargoInfo,
                'searchText' => $this->searchText([
                    $cargo->tracking_number,
                    $cargoInfo['description'],
                    $cargoInfo['cargoType'],
                    $cargoInfo['customerName'],
                    $cargoInfo['transporterName'],
                    $cargo->origin_city,
                    $cargo->origin_address,
                    $cargo->destination_city,
                    $cargo->destination_address,
                    $cargo->current_location_city,
                    $cargo->statusLabel(),
                ]),
            ];
        }

        return [
            'mode' => 'manager',
            'stats' => [
                'cargoes' => $cargoes->count(),
                'stores' => count($storeMarkers),
                'lastRefreshedAt' => now()->format('d M Y H:i:s'),
            ],
            'markers' => [
                ...array_values($storeMarkers),
                ...$cargoMarkers,
            ],
        ];
    }

    private function cargoPayload(Request $request, Cargo $cargo): array
    {
        $origin = $this->areaCoordinates($cargo->origin_city);
        $destination = $this->areaCoordinates($cargo->destination_city);
        $current = $this->currentCoordinates($cargo, $origin, $destination);
        $cargoInfo = $this->cargoInfo($cargo, $current);

        return [
            'mode' => $this->trackingMode($request),
            'cargoId' => $cargo->id,
            'trackingNumber' => $cargo->tracking_number,
            'rawStatus' => $cargo->status,
            'status' => $cargo->statusLabel(),
            'statusClass' => $cargo->statusBadgeClass(),
            'currentLocationLabel' => $cargoInfo['currentLocationLabel'],
            'currentLocationTime' => $cargoInfo['currentLocationTime'],
            'movementCount' => $cargoInfo['movementCount'],
            'locationUrl' => route('dashboard.cargo-map.location', $cargo),
            'routeWaypoints' => [$origin, $destination],
            'cargo' => $cargoInfo,
            'markers' => [
                [
                    'key' => "pickup:{$cargo->id}",
                    'entityType' => 'store',
                    'variant' => 'pickup',
                    'storeType' => 'Pickup Store',
                    'iconClass' => 'bi-shop',
                    'title' => $cargo->origin_city,
                    'subtitle' => $cargo->origin_address ?: 'Pickup point',
                    'position' => $origin,
                    'cargoCount' => 1,
                    'cargoes' => [$this->storeCargoSummary($cargo)],
                    'searchText' => $this->searchText([$cargo->origin_city, $cargo->origin_address, 'pickup store']),
                ],
                [
                    'key' => "destination:{$cargo->id}",
                    'entityType' => 'store',
                    'variant' => 'destination',
                    'storeType' => 'Destination Store',
                    'iconClass' => 'bi-shop',
                    'title' => $cargo->destination_city,
                    'subtitle' => $cargo->destination_address ?: 'Destination point',
                    'position' => $destination,
                    'cargoCount' => 1,
                    'cargoes' => [$this->storeCargoSummary($cargo)],
                    'searchText' => $this->searchText([$cargo->destination_city, $cargo->destination_address, 'destination store']),
                ],
                [
                    'key' => "cargo:{$cargo->id}",
                    'entityType' => 'cargo',
                    'variant' => 'cargo',
                    'iconClass' => 'bi-box-seam',
                    'title' => $cargo->tracking_number,
                    'subtitle' => $cargoInfo['currentLocationLabel'],
                    'position' => $current,
                    'routeWaypoints' => [$origin, $destination],
                    'cargo' => $cargoInfo,
                    'searchText' => $this->searchText([
                        $cargo->tracking_number,
                        $cargoInfo['description'],
                        $cargoInfo['currentLocationLabel'],
                        $cargo->statusLabel(),
                    ]),
                ],
            ],
        ];
    }

    private function appendStoreMarker(array &$storeMarkers, Cargo $cargo, string $variant, string $storeType, array $position): void
    {
        $city = $variant === 'pickup' ? $cargo->origin_city : $cargo->destination_city;
        $address = $variant === 'pickup' ? $cargo->origin_address : $cargo->destination_address;
        $country = $variant === 'pickup' ? $cargo->origin_country : $cargo->destination_country;
        $key = 'store:'.md5($variant.'|'.strtolower((string) $city).'|'.strtolower((string) $address));

        if (! isset($storeMarkers[$key])) {
            $storeMarkers[$key] = [
                'key' => $key,
                'entityType' => 'store',
                'variant' => $variant,
                'storeType' => $storeType,
                'iconClass' => 'bi-shop',
                'title' => $city,
                'subtitle' => $address ?: $country,
                'position' => $position,
                'cargoCount' => 0,
                'cargoes' => [],
                'searchText' => $this->searchText([$storeType, $city, $address, $country]),
            ];
        }

        $storeMarkers[$key]['cargoCount']++;
        $storeMarkers[$key]['cargoes'][] = $this->storeCargoSummary($cargo);
        $storeMarkers[$key]['searchText'] .= ' '.$this->searchText([
            $cargo->tracking_number,
            $cargo->statusLabel(),
            $cargo->detail?->description,
            $this->displayName($cargo->customer),
        ]);
    }

    private function storeCargoSummary(Cargo $cargo): array
    {
        return [
            'id' => $cargo->id,
            'trackingNumber' => $cargo->tracking_number,
            'rawStatus' => $cargo->status,
            'status' => $cargo->statusLabel(),
            'statusClass' => $cargo->statusBadgeClass(),
            'description' => $cargo->detail?->description ?: '-',
            'customerName' => $this->displayName($cargo->customer),
            'route' => "{$cargo->origin_city} to {$cargo->destination_city}",
        ];
    }

    private function cargoInfo(Cargo $cargo, array $current): array
    {
        $detail = $cargo->detail;
        $transportUser = $cargo->transportStaff?->user;
        $latestUpdate = $cargo->locationUpdates->first();

        return [
            'id' => $cargo->id,
            'trackingNumber' => $cargo->tracking_number,
            'status' => $cargo->statusLabel(),
            'statusClass' => $cargo->statusBadgeClass(),
            'description' => $detail?->description ?: '-',
            'cargoType' => $detail?->cargo_type ?: '-',
            'weightKg' => number_format((float) ($detail?->weight_kg ?? 0), 2),
            'volumeCbm' => $detail?->volume_cbm !== null ? number_format((float) $detail->volume_cbm, 2) : '-',
            'quantity' => $detail?->quantity ?? '-',
            'packageCount' => $detail?->package_count ?? '-',
            'estimatedValue' => $detail?->estimated_value !== null ? number_format((float) $detail->estimated_value, 2) : '-',
            'isFragile' => (bool) ($detail?->is_fragile ?? false),
            'isHazardous' => (bool) ($detail?->is_hazardous ?? false),
            'specialInstructions' => $detail?->special_instructions ?: '-',
            'customerName' => $this->displayName($cargo->customer),
            'customerEmail' => $cargo->customer?->email ?: '-',
            'customerPhone' => $cargo->customer?->phone ?: '-',
            'transporterName' => $this->displayName($transportUser) ?: 'Not assigned yet',
            'transporterPhone' => $transportUser?->phone ?: '-',
            'staffCode' => $cargo->transportStaff?->staff_code ?: '-',
            'vehicle' => trim(($cargo->transportStaff?->vehicle_type ?: '').' '.($cargo->transportStaff?->vehicle_plate ?: '')) ?: '-',
            'originCountry' => strtoupper((string) $cargo->origin_country),
            'originCity' => $cargo->origin_city,
            'originAddress' => $cargo->origin_address ?: '-',
            'destinationCountry' => strtoupper((string) $cargo->destination_country),
            'destinationCity' => $cargo->destination_city,
            'destinationAddress' => $cargo->destination_address ?: '-',
            'pickupDate' => optional($cargo->pickup_date)->format('d M Y') ?: '-',
            'deliveryDate' => optional($cargo->delivery_date)->format('d M Y') ?: '-',
            'currentLocationLabel' => $this->currentLocationLabel($cargo),
            'currentLocationTime' => optional($latestUpdate?->recorded_at ?: $cargo->current_location_updated_at)->format('d M Y H:i') ?: '-',
            'currentLatitude' => number_format((float) $current[0], 7, '.', ''),
            'currentLongitude' => number_format((float) $current[1], 7, '.', ''),
            'movementCount' => (int) ($cargo->location_updates_count ?? 0),
            'signedAt' => optional($cargo->signed_at)->format('d M Y H:i') ?: '-',
            'handoverConfirmedAt' => optional($cargo->handover_confirmed_at)->format('d M Y H:i') ?: '-',
            'approvalNote' => $cargo->approval_note ?: '-',
        ];
    }

    private function statusProgress(string $status): float
    {
        return match ($status) {
            Cargo::STATUS_PENDING => 0.03,
            Cargo::STATUS_APPROVED => 0.12,
            Cargo::STATUS_IN_TRANSIT => 0.55,
            Cargo::STATUS_ARRIVED_REGIONAL_HUB => 0.7,
            Cargo::STATUS_ARRIVED => 0.92,
            Cargo::STATUS_DELIVERED => 1.0,
            default => 0.0,
        };
    }

    private function currentLocationLabel(Cargo $cargo): string
    {
        $latestUpdate = $cargo->locationUpdates->first();

        if ($latestUpdate?->location_name) {
            return $latestUpdate->location_name;
        }

        if ($cargo->current_location_city) {
            return $cargo->current_location_city;
        }

        if (
            in_array($cargo->status, [Cargo::STATUS_IN_TRANSIT, Cargo::STATUS_ARRIVED_REGIONAL_HUB], true)
            && $cargo->current_location_lat !== null
            && $cargo->current_location_lng !== null
        ) {
            return 'Stored route location';
        }

        return match ($cargo->status) {
            Cargo::STATUS_PENDING, Cargo::STATUS_APPROVED => $cargo->origin_city,
            Cargo::STATUS_IN_TRANSIT, Cargo::STATUS_ARRIVED_REGIONAL_HUB => "{$cargo->origin_city} to {$cargo->destination_city}",
            Cargo::STATUS_ARRIVED, Cargo::STATUS_DELIVERED => $cargo->destination_city,
            default => $cargo->origin_city,
        };
    }

    private function currentCoordinates(Cargo $cargo, array $origin, array $destination): array
    {
        $latestUpdate = $cargo->locationUpdates->first();

        if ($latestUpdate && $this->isValidPosition([(float) $latestUpdate->latitude, (float) $latestUpdate->longitude])) {
            return [
                (float) $latestUpdate->latitude,
                (float) $latestUpdate->longitude,
            ];
        }

        if ($cargo->current_location_lat !== null && $cargo->current_location_lng !== null) {
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

    private function areaCoordinates(?string $city): array
    {
        return self::AREA_COORDINATES[$city] ?? [-6.3690, 34.8888];
    }

    private function isValidPosition(array $position): bool
    {
        return count($position) === 2
            && is_finite((float) $position[0])
            && is_finite((float) $position[1]);
    }

    private function displayName($user): string
    {
        return (string) ($user?->full_name ?: $user?->name ?: '');
    }

    private function searchText(array $values): string
    {
        return strtolower(implode(' ', array_filter(array_map(
            fn ($value) => is_scalar($value) ? (string) $value : '',
            $values
        ))));
    }
}
