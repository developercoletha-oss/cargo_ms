@extends('layouts.app')

@section('title', 'Track Cargo - CFTMS')

@push('critical-head')
    @if($trackingPayload)
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    @endif
    <style>
        .tracking-page {
            background: #f8fafc;
            min-height: 70vh;
            padding: 70px 0;
        }

        .tracking-shell {
            max-width: 920px;
            margin: 0 auto;
        }

        .tracking-number {
            font-size: 1.15rem;
            letter-spacing: 0;
        }

        .tracking-timeline {
            display: grid;
            gap: 1rem;
        }

        .tracking-step {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .tracking-step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .live-tracking-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 280px;
            gap: 1rem;
        }

        .live-tracking-map {
            height: 480px;
            min-height: 420px;
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            background: #e2e8f0;
        }

        .tracking-stat-list {
            display: grid;
            gap: 0.75rem;
        }

        .tracking-stat {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem;
            background: #ffffff;
        }

        .tracking-stat > span {
            display: block;
            color: #64748b;
            font-size: 0.78rem;
        }

        .tracking-stat > strong {
            display: block;
            color: #0f172a;
            font-size: 0.95rem;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .live-truck-marker {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            color: #ffffff;
            background: #b45309;
            border: 2px solid #ffffff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.28);
            transform-origin: center;
        }

        .live-truck-marker i {
            font-size: 1.2rem;
            line-height: 1;
        }

        .live-tracking-popup {
            min-width: 240px;
        }

        .live-tracking-popup-title {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.35rem;
        }

        .live-tracking-popup-grid {
            display: grid;
            gap: 0.25rem;
            font-size: 0.82rem;
        }

        @media (max-width: 991.98px) {
            .live-tracking-grid {
                grid-template-columns: 1fr;
            }

            .live-tracking-map {
                height: 58vh;
                min-height: 360px;
            }
        }
    </style>
@endpush

@section('content')
    <section class="tracking-page">
        <div class="container">
            <div class="tracking-shell">
                <div class="mb-4">
                    <h1 class="fw-bold mb-2">Track Cargo</h1>
                    <p class="text-muted mb-0">Enter your cargo tracking number to view the current movement status.</p>
                </div>

                <form method="GET" action="{{ route('tracking.show') }}" class="mb-4">
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" name="tracking_number" class="form-control" value="{{ $trackingNumber }}" placeholder="Example: CFTMS-2026-AB12CD34" required>
                        <button class="btn btn-primary" type="submit">Track</button>
                    </div>
                </form>

                @if($searched && ! $cargo)
                    <div class="alert alert-warning shadow-sm">
                        No cargo found for tracking number <strong>{{ $trackingNumber }}</strong>.
                    </div>
                @endif

                @if($cargo)
                    @php
                        $statusClass = $cargo->statusBadgeClass();
                        $hasApproved = in_array($cargo->status, ['approved', 'in_transit', 'arrived', 'delivered'], true);
                        $hasInTransit = in_array($cargo->status, ['in_transit', 'arrived', 'delivered'], true);
                        $hasArrived = in_array($cargo->status, ['arrived', 'delivered'], true);
                        $hasDelivered = $cargo->status === 'delivered';
                    @endphp
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
                                <div>
                                    <div class="text-muted small">Tracking Number</div>
                                    <strong class="tracking-number">{{ $cargo->tracking_number }}</strong>
                                </div>
                                <div>
                                    <span class="badge text-bg-{{ $statusClass }} fs-6">{{ $cargo->statusLabel() }}</span>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6"><strong>From:</strong> {{ strtoupper($cargo->origin_country) }} / {{ $cargo->origin_city }}</div>
                                <div class="col-md-6"><strong>To:</strong> {{ strtoupper($cargo->destination_country) }} / {{ $cargo->destination_city }}</div>
                                <div class="col-md-6"><strong>Pickup Date:</strong> {{ optional($cargo->pickup_date)->format('d M Y') ?: '-' }}</div>
                                <div class="col-md-6"><strong>Delivery Date:</strong> {{ optional($cargo->delivery_date)->format('d M Y') ?: '-' }}</div>
                                <div class="col-md-6"><strong>Weight:</strong> {{ number_format((float) ($cargo->detail?->weight_kg ?? 0), 2) }} kg</div>
                                <div class="col-md-6"><strong>Packages:</strong> {{ $cargo->detail?->package_count ?? '-' }}</div>
                                <div class="col-md-6"><strong>Current Location:</strong> {{ $cargo->current_location_city ?: ($cargo->current_location_lat && $cargo->current_location_lng ? 'Stored route location' : '-') }}</div>
                                <div class="col-md-6"><strong>Location Updated:</strong> {{ optional($cargo->current_location_updated_at)->format('d M Y H:i') ?: '-' }}</div>
                                <div class="col-12"><strong>Description:</strong> {{ $cargo->detail?->description }}</div>
                                <div class="col-12"><strong>Transport Officer:</strong> {{ $cargo->transportStaff?->user?->full_name ?: $cargo->transportStaff?->user?->name ?: 'Not assigned yet' }}</div>
                            </div>
                        </div>
                    </div>

                    @if($trackingPayload)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                                    <div>
                                        <h2 class="h5 fw-bold mb-1">Live Route Simulation</h2>
                                        <div class="text-muted small">OpenStreetMap follows the cargo movement in real time.</div>
                                    </div>
                                    <span class="badge text-bg-{{ $statusClass }}">Automatic updates</span>
                                </div>
                                <div class="live-tracking-grid">
                                    <div id="customerLiveTrackingMap" class="live-tracking-map"></div>
                                    <div class="tracking-stat-list">
                                        <div class="tracking-stat">
                                            <span>Current Location</span>
                                            <strong id="trackingCurrentLocation">{{ $trackingPayload['currentLocation'] }}</strong>
                                        </div>
                                        <div class="tracking-stat">
                                            <span>Latitude / Longitude</span>
                                            <strong><span id="trackingCurrentLatitude">{{ $trackingPayload['currentLatitude'] }}</span>, <span id="trackingCurrentLongitude">{{ $trackingPayload['currentLongitude'] }}</span></strong>
                                        </div>
                                        <div class="tracking-stat">
                                            <span>Distance Travelled</span>
                                            <strong id="trackingDistanceTravelled">0.00 km</strong>
                                        </div>
                                        <div class="tracking-stat">
                                            <span>Remaining Distance</span>
                                            <strong id="trackingRemainingDistance">Calculating...</strong>
                                        </div>
                                        <div class="tracking-stat">
                                            <span>ETA</span>
                                            <strong id="trackingEta">Calculating...</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h2 class="h5 fw-bold mb-3">Movement Progress</h2>
                            <div class="tracking-timeline">
                                <div class="tracking-step">
                                    <div class="tracking-step-icon bg-success text-white"><i class="bi bi-check-lg"></i></div>
                                    <div>
                                        <strong>Cargo registered</strong>
                                        <div class="text-muted">{{ optional($cargo->created_at)->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                                <div class="tracking-step">
                                    <div class="tracking-step-icon {{ $hasApproved ? 'bg-success text-white' : 'bg-secondary-subtle text-secondary' }}"><i class="bi bi-clipboard-check"></i></div>
                                    <div>
                                        <strong>Approval</strong>
                                        <div class="text-muted">{{ $cargo->approved_at ? 'Approved on ' . optional($cargo->approved_at)->format('d M Y H:i') : 'Waiting for approval' }}</div>
                                    </div>
                                </div>
                                <div class="tracking-step">
                                    <div class="tracking-step-icon {{ $cargo->transport_staff_id ? 'bg-success text-white' : 'bg-secondary-subtle text-secondary' }}"><i class="bi bi-person-check"></i></div>
                                    <div>
                                        <strong>Transport assigned</strong>
                                        <div class="text-muted">{{ $cargo->transportStaff ? ($cargo->transportStaff->user?->full_name ?: $cargo->transportStaff->user?->name) : 'Not assigned yet' }}</div>
                                    </div>
                                </div>
                                <div class="tracking-step">
                                    <div class="tracking-step-icon {{ $hasInTransit ? 'bg-success text-white' : 'bg-secondary-subtle text-secondary' }}"><i class="bi bi-truck"></i></div>
                                    <div>
                                        <strong>In transit</strong>
                                        <div class="text-muted">{{ $hasInTransit ? 'Cargo is on the way' : 'Waiting for warehouse handover' }}</div>
                                    </div>
                                </div>
                                <div class="tracking-step">
                                    <div class="tracking-step-icon {{ $hasArrived ? 'bg-success text-white' : 'bg-secondary-subtle text-secondary' }}"><i class="bi bi-geo-alt"></i></div>
                                    <div>
                                        <strong>Arrived</strong>
                                        <div class="text-muted">{{ $hasArrived ? 'Cargo has arrived at destination area' : 'Not arrived yet' }}</div>
                                    </div>
                                </div>
                                <div class="tracking-step">
                                    <div class="tracking-step-icon {{ $hasDelivered ? 'bg-success text-white' : 'bg-secondary-subtle text-secondary' }}"><i class="bi bi-check2-circle"></i></div>
                                    <div>
                                        <strong>Delivered</strong>
                                        <div class="text-muted">{{ $hasDelivered ? 'Cargo delivered successfully' : 'Not delivered yet' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@push('scripts')
@if($trackingPayload)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tracking = @json($trackingPayload);
    const route = (tracking.routeCoordinates || [])
        .filter((point) => Array.isArray(point) && point.length === 2)
        .map((point) => [Number(point[0]), Number(point[1])])
        .filter((point) => Number.isFinite(point[0]) && Number.isFinite(point[1]));

    if (route.length < 2 || !window.L) return;

    const map = L.map('customerLiveTrackingMap', {
        scrollWheelZoom: true,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    const escapeHtml = (value) => {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    };

    const totalDistance = route.slice(1).reduce((sum, point, index) => {
        return sum + map.distance(route[index], point);
    }, 0);

    const routeDuration = Math.max(1, route.length - 1) * Number(tracking.animationIntervalMs || 3000);
    const routeLocations = tracking.routeLocations || [];
    const remainingLine = L.polyline(route, {
        color: '#2563eb',
        opacity: 0.75,
        weight: 5,
    }).addTo(map);
    const travelledLine = L.polyline([route[0]], {
        color: '#16a34a',
        opacity: 0.95,
        weight: 6,
    }).addTo(map);

    const startMarker = L.marker(route[0], {
        icon: L.divIcon({
            className: '',
            html: '<div class="live-truck-marker" style="background:#0f766e"><i class="bi bi-geo-alt-fill"></i></div>',
            iconSize: [34, 34],
            iconAnchor: [17, 17],
        }),
        title: tracking.origin || 'Origin',
    }).addTo(map);

    const endMarker = L.marker(route[route.length - 1], {
        icon: L.divIcon({
            className: '',
            html: '<div class="live-truck-marker" style="background:#7c3aed"><i class="bi bi-flag-fill"></i></div>',
            iconSize: [34, 34],
            iconAnchor: [17, 17],
        }),
        title: tracking.destination || 'Destination',
    }).addTo(map);

    startMarker.bindPopup(`<strong>Origin:</strong> ${escapeHtml(tracking.origin || '-')}`);
    endMarker.bindPopup(`<strong>Destination:</strong> ${escapeHtml(tracking.destination || '-')}`);

    const truckIcon = (bearing) => L.divIcon({
        className: '',
        html: `<div class="live-truck-marker" style="transform: rotate(${bearing}deg)"><i class="bi bi-truck"></i></div>`,
        iconSize: [38, 38],
        iconAnchor: [19, 19],
        popupAnchor: [0, -20],
    });

    const truckMarker = L.marker(route[0], {
        icon: truckIcon(0),
        title: tracking.cargoId || 'Cargo',
    }).addTo(map);

    const setText = (id, value) => {
        const node = document.getElementById(id);
        if (node) node.textContent = value;
    };

    const formatKm = (meters) => `${(meters / 1000).toFixed(2)} km`;

    const interpolate = (from, to, progress) => [
        from[0] + ((to[0] - from[0]) * progress),
        from[1] + ((to[1] - from[1]) * progress),
    ];

    const bearingBetween = (from, to) => {
        const fromLat = from[0] * Math.PI / 180;
        const toLat = to[0] * Math.PI / 180;
        const lngDelta = (to[1] - from[1]) * Math.PI / 180;
        const y = Math.sin(lngDelta) * Math.cos(toLat);
        const x = Math.cos(fromLat) * Math.sin(toLat) - Math.sin(fromLat) * Math.cos(toLat) * Math.cos(lngDelta);

        return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
    };

    const popupHtml = (location, travelled, remaining, eta) => `
        <div class="live-tracking-popup">
            <div class="live-tracking-popup-title">${escapeHtml(tracking.cargoId)}</div>
            <div class="mb-2"><span class="badge text-bg-${escapeHtml(tracking.statusClass || 'secondary')}">${escapeHtml(tracking.status)}</span></div>
            <div class="live-tracking-popup-grid">
                <div><strong>Current Location:</strong> ${escapeHtml(location)}</div>
                <div><strong>Distance Travelled:</strong> ${escapeHtml(formatKm(travelled))}</div>
                <div><strong>ETA:</strong> ${escapeHtml(eta)}</div>
            </div>
        </div>
    `;

    const positionAtProgress = (progress) => {
        const scaled = progress * (route.length - 1);
        const index = Math.min(Math.floor(scaled), route.length - 2);
        const segmentProgress = scaled - index;
        const position = interpolate(route[index], route[index + 1], segmentProgress);
        const travelledRoute = route.slice(0, index + 1);

        travelledRoute.push(position);

        return {
            index,
            position,
            travelledRoute,
            remainingRoute: [position, ...route.slice(index + 1)],
            bearing: bearingBetween(route[index], route[index + 1]),
            location: routeLocations[index] || tracking.currentLocation || `${tracking.origin} to ${tracking.destination}`,
        };
    };

    const updateView = (timestamp, startedAt) => {
        const elapsed = (timestamp - startedAt) % routeDuration;
        const progress = elapsed / routeDuration;
        const state = positionAtProgress(progress);
        const travelled = totalDistance * progress;
        const remaining = Math.max(0, totalDistance - travelled);
        const etaMinutes = Math.max(1, Math.ceil((routeDuration - elapsed) / 60000));
        const eta = etaMinutes === 1 ? 'About 1 minute' : `About ${etaMinutes} minutes`;
        const [lat, lng] = state.position;

        truckMarker.setLatLng(state.position);
        truckMarker.setIcon(truckIcon(state.bearing));
        truckMarker.setPopupContent(popupHtml(state.location, travelled, remaining, eta));
        travelledLine.setLatLngs(state.travelledRoute);
        remainingLine.setLatLngs(state.remainingRoute);
        map.panTo(state.position, { animate: true, duration: 0.8 });

        setText('trackingCurrentLocation', state.location);
        setText('trackingCurrentLatitude', lat.toFixed(7));
        setText('trackingCurrentLongitude', lng.toFixed(7));
        setText('trackingDistanceTravelled', formatKm(travelled));
        setText('trackingRemainingDistance', formatKm(remaining));
        setText('trackingEta', eta);

        window.requestAnimationFrame((nextTimestamp) => updateView(nextTimestamp, startedAt));
    };

    map.fitBounds(L.latLngBounds(route), {
        padding: [36, 36],
        maxZoom: 9,
    });

    truckMarker.bindPopup(popupHtml(tracking.currentLocation, 0, totalDistance, 'Calculating...')).openPopup();

    window.setTimeout(() => {
        map.invalidateSize();
        window.requestAnimationFrame((timestamp) => updateView(timestamp, timestamp));
    }, 150);
});
</script>
@endif
@endpush
