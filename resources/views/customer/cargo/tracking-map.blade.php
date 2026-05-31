@extends('layouts.colethaDashboardLayout')

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3">
    <div class="row g-3 align-items-stretch">
        <div class="col-lg-3 col-xl-3">
            <section class="card border-0 shadow-sm h-100 cargo-list-panel">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h2 class="h5 fw-bold mb-1">My Cargo</h2>
                            <p class="text-muted small mb-0">Select cargo to view movement on map.</p>
                        </div>
                        <span class="badge text-bg-primary">{{ $cargoes->count() }}</span>
                    </div>

                    <div class="list-group list-group-flush cargo-list-scroll">
                        @forelse($cargoes as $cargo)
                            <a href="{{ route('dashboard.cargo-map', ['cargo_id' => $cargo->id]) }}"
                                class="list-group-item list-group-item-action px-0 {{ $selectedCargo && (int) $selectedCargo->id === (int) $cargo->id ? 'active rounded px-3' : '' }}">
                                <div class="d-flex justify-content-between gap-2">
                                    <strong>{{ $cargo->tracking_number }}</strong>
                                    <span class="badge text-bg-{{ $cargo->statusBadgeClass() }}">{{ $cargo->statusLabel() }}</span>
                                </div>
                                <div class="{{ $selectedCargo && (int) $selectedCargo->id === (int) $cargo->id ? 'text-white-50' : 'text-muted' }} small mt-1">
                                    {{ $cargo->origin_city }} to {{ $cargo->destination_city }}
                                </div>
                            </a>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-box-seam fs-3 d-block mb-2"></i>
                                No registered cargo yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>

        <div class="col-lg-9 col-xl-9">
            <section class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    @if($selectedCargo && $mapPayload)
                        <div class="p-3 border-bottom">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div>
                                    <div class="text-muted small">Tracking Number</div>
                                    <h2 class="h5 fw-bold mb-0">{{ $selectedCargo->tracking_number }}</h2>
                                </div>
                                <span class="badge text-bg-{{ $selectedCargo->statusBadgeClass() }} fs-6">{{ $selectedCargo->statusLabel() }}</span>
                            </div>
                            <div class="row g-2 mt-3 small">
                                <div class="col-md-6"><strong>From:</strong> {{ $selectedCargo->origin_city }}</div>
                                <div class="col-md-6"><strong>To:</strong> {{ $selectedCargo->destination_city }}</div>
                                <div class="col-md-6"><strong>Cargo:</strong> {{ $selectedCargo->detail?->description ?: '-' }}</div>
                                <div class="col-md-6"><strong>Transporter:</strong> {{ $selectedCargo->transportStaff?->user?->full_name ?: $selectedCargo->transportStaff?->user?->name ?: 'Not assigned yet' }}</div>
                                <div class="col-md-6"><strong>Current Location:</strong> <span id="currentLocationLabel">{{ $mapPayload['currentLocationLabel'] }}</span></div>
                                <div class="col-md-6"><strong>Last Update:</strong> <span id="currentLocationTime">{{ $mapPayload['currentLocationTime'] ?: '-' }}</span></div>
                                <div class="col-md-6"><strong>Saved Movements:</strong> <span id="movementCount">{{ count($mapPayload['movementPoints']) }}</span></div>
                            </div>
                        </div>
                        <div class="cargo-map-wrap">
                            <div id="cargoTrackingMap" class="cargo-tracking-map"></div>
                        </div>
                        <div class="p-3 border-top">
                            <div class="text-muted small">
                                Green dotted line shows saved movement points reported during transport.
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-map fs-1 d-block mb-3"></i>
                            No cargo is available for map tracking yet.
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    .cargo-tracking-map {
        height: min(68vh, 620px);
        min-height: 420px;
        width: 100%;
    }

    .cargo-list-panel {
        position: sticky;
        top: 88px;
    }

    .cargo-list-scroll {
        max-height: min(68vh, 620px);
        overflow-y: auto;
        padding-right: 0.25rem;
    }

    .cargo-map-wrap {
        display: flex;
        justify-content: center;
        background: #f8fafc;
    }

    .cargo-current-marker {
        width: 18px;
        height: 18px;
        border-radius: 999px;
        background: #2563eb;
        border: 3px solid #ffffff;
        box-shadow: 0 0 0 6px rgba(37, 99, 235, 0.18);
    }

    @media (max-width: 991.98px) {
        .cargo-list-panel {
            position: static;
        }

        .cargo-list-scroll {
            max-height: 280px;
        }
    }
</style>
@endpush

@push('scripts')
@if($selectedCargo && $mapPayload)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const payload = @json($mapPayload);
    const map = L.map('cargoTrackingMap', {
        scrollWheelZoom: true,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    const route = L.polyline(payload.route, {
        color: '#2563eb',
        weight: 5,
        opacity: 0.78,
    }).addTo(map);

    const movementTrail = L.polyline(payload.movementTrail || [], {
        color: '#16a34a',
        weight: 4,
        opacity: 0.85,
        dashArray: '8 8',
    }).addTo(map);

    L.marker(payload.origin).addTo(map)
        .bindPopup(`<strong>Origin</strong><br>${payload.originLabel}`);

    L.marker(payload.destination).addTo(map)
        .bindPopup(`<strong>Destination</strong><br>${payload.destinationLabel}`);

    const currentIcon = L.divIcon({
        className: '',
        html: '<div class="cargo-current-marker"></div>',
        iconSize: [18, 18],
        iconAnchor: [9, 9],
    });

    const currentMarker = L.marker(payload.current, { icon: currentIcon }).addTo(map)
        .bindPopup(`<strong>${payload.currentLocationLabel}</strong><br>${payload.trackingNumber}<br>Status: ${payload.status}<br>Updated: ${payload.currentLocationTime || '-'}`)
        .openPopup();

    map.fitBounds(route.getBounds(), {
        padding: [40, 40],
        maxZoom: 8,
    });

    const locationLabel = document.getElementById('currentLocationLabel');
    const locationTime = document.getElementById('currentLocationTime');
    const movementCount = document.getElementById('movementCount');

    const renderMovementTrail = (latest) => {
        movementTrail.setLatLngs(latest.movementTrail || []);

        if (movementCount) {
            movementCount.textContent = (latest.movementPoints || []).length;
        }
    };

    renderMovementTrail(payload);

    const refreshLiveLocation = async () => {
        if (!payload.locationUrl) return;

        const response = await fetch(payload.locationUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            cache: 'no-store',
        });

        if (!response.ok) return;

        const latest = await response.json();
        currentMarker.setLatLng(latest.current);
        currentMarker.setPopupContent(`<strong>${latest.currentLocationLabel}</strong><br>${latest.trackingNumber}<br>Status: ${latest.status}<br>Updated: ${latest.currentLocationTime || '-'}`);
        renderMovementTrail(latest);

        if (locationLabel) locationLabel.textContent = latest.currentLocationLabel;
        if (locationTime) locationTime.textContent = latest.currentLocationTime || '-';
    };

    window.setInterval(() => {
        refreshLiveLocation().catch(() => {});
    }, 10000);
});
</script>
@endif
@endpush
