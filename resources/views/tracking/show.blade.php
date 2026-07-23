@extends('layouts.app')

@section('title', 'Track Cargo - CFTMS')

@push('critical-head')
    @if($trackingPayload)
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css">
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

        .live-tracking-map-message {
            align-items: center;
            color: #475569;
            display: flex;
            font-weight: 600;
            height: 100%;
            justify-content: center;
            padding: 1rem;
            text-align: center;
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

        .leaflet-routing-container {
            display: none;
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
                                        <h2 class="h5 fw-bold mb-1">Live Road Route</h2>
                                        <div class="text-muted small">OpenStreetMap follows live GPS updates along the driving route.</div>
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
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tracking = @json($trackingPayload);
    const origin = normalizePoint(tracking.originCoordinates);
    const destination = normalizePoint(tracking.destinationCoordinates);
    const initialTruckPosition = normalizePoint([
        tracking.currentLatitude,
        tracking.currentLongitude,
    ]) || origin;

    const escapeHtml = (value) => {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    };

    const mapElement = document.getElementById('customerLiveTrackingMap');
    const showMapMessage = (message) => {
        if (mapElement) {
            mapElement.innerHTML = `<div class="live-tracking-map-message">${escapeHtml(message)}</div>`;
        }
    };

    if (!origin || !destination) {
        showMapMessage('Route coordinates are not available for this cargo.');
        return;
    }

    if (!window.L) {
        showMapMessage('Leaflet could not load. Please check your internet connection and refresh.');
        return;
    }

    if (!L.Routing) {
        showMapMessage('Leaflet Routing Machine could not load. Please check the CDN connection and refresh.');
        return;
    }

    const map = L.map('customerLiveTrackingMap', {
        scrollWheelZoom: true,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    let routeLine = null;
    let routeSummary = null;
    let previousTruckPosition = initialTruckPosition;
    let latestStatus = tracking.status || '-';
    let latestStatusClass = tracking.statusClass || 'secondary';
    let pollingTimer = null;

    function normalizePoint(point) {
        if (!Array.isArray(point) || point.length !== 2) return null;

        const lat = Number(point[0]);
        const lng = Number(point[1]);

        return Number.isFinite(lat) && Number.isFinite(lng) ? [lat, lng] : null;
    }

    const startMarker = L.marker(origin, {
        icon: L.divIcon({
            className: '',
            html: '<div class="live-truck-marker" style="background:#0f766e"><i class="bi bi-geo-alt-fill"></i></div>',
            iconSize: [34, 34],
            iconAnchor: [17, 17],
        }),
        title: tracking.origin || 'Origin',
    }).addTo(map);

    const endMarker = L.marker(destination, {
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

    const truckMarker = L.marker(initialTruckPosition, {
        icon: truckIcon(0),
        title: tracking.cargoId || 'Cargo',
    }).addTo(map);

    const setText = (id, value) => {
        const node = document.getElementById(id);
        if (node) node.textContent = value;
    };

    const formatKm = (meters) => `${(meters / 1000).toFixed(2)} km`;

    const normalizeStatus = (status) => String(status || '')
        .trim()
        .toLowerCase()
        .replace(/[\s-]+/g, '_');

    const isTransitStatus = (payload) => {
        const normalizedStatus = normalizeStatus(payload.status);
        const normalizedRawStatus = normalizeStatus(payload.rawStatus);

        return ['in_transit', 'on_transit'].includes(normalizedStatus)
            || ['in_transit', 'on_transit'].includes(normalizedRawStatus);
    };

    const formatLastUpdated = (payload) => {
        const recordedAt = payload.latestLocationUpdate?.recordedAt || payload.currentLocationUpdatedAt;
        if (!recordedAt) return '-';

        const recordedTime = new Date(recordedAt).getTime();
        if (!Number.isFinite(recordedTime)) return '-';

        const seconds = Math.max(0, Math.floor((Date.now() - recordedTime) / 1000));
        if (seconds < 60) return 'Just now';

        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return `${minutes} min${minutes === 1 ? '' : 's'} ago`;

        const hours = Math.floor(minutes / 60);
        if (hours < 24) return `${hours} hour${hours === 1 ? '' : 's'} ago`;

        return new Date(recordedAt).toLocaleString();
    };

    const interpolatePoint = (from, to, progress) => [
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

    const popupHtml = (payload, remaining) => `
        <div class="live-tracking-popup">
            <div class="live-tracking-popup-title">${escapeHtml(tracking.cargoId)}</div>
            <div class="mb-2"><span class="badge text-bg-${escapeHtml(latestStatusClass)}">${escapeHtml(latestStatus)}</span></div>
            <div class="live-tracking-popup-grid">
                <div><strong>Current Location:</strong> ${escapeHtml(payload.currentLocation || '-')}</div>
                <div><strong>Last Updated:</strong> ${escapeHtml(formatLastUpdated(payload))}</div>
                <div><strong>Coordinates:</strong> ${escapeHtml(payload.currentLatitude)}, ${escapeHtml(payload.currentLongitude)}</div>
                <div><strong>Remaining:</strong> ${escapeHtml(formatKm(remaining))}</div>
            </div>
        </div>
    `;

    const routingControl = L.Routing.control({
        waypoints: [
            L.latLng(origin[0], origin[1]),
            L.latLng(destination[0], destination[1]),
        ],
        router: L.Routing.osrmv1({
            serviceUrl: 'https://router.project-osrm.org/route/v1',
            profile: 'driving',
        }),
        lineOptions: {
            styles: [
                { color: '#1d4ed8', opacity: 0.85, weight: 6 },
                { color: '#ffffff', opacity: 0.65, weight: 2 },
            ],
        },
        addWaypoints: false,
        draggableWaypoints: false,
        fitSelectedRoutes: true,
        routeWhileDragging: false,
        show: false,
        createMarker: () => null,
    }).addTo(map);

    routingControl.on('routesfound', (event) => {
        const route = event.routes[0];
        routeLine = route.coordinates || [];
        routeSummary = route.summary || null;
        updateRouteStats(truckMarker.getLatLng());
    });

    routingControl.on('routingerror', () => {
        map.fitBounds(L.latLngBounds([origin, destination]), {
            padding: [36, 36],
            maxZoom: 9,
        });
    });

    const distanceAlongRouteTo = (latLng) => {
        if (!routeLine || routeLine.length < 2) {
            return map.distance(origin, [latLng.lat, latLng.lng]);
        }

        let travelled = 0;
        let nearestIndex = 0;
        let nearestDistance = Infinity;

        routeLine.forEach((point, index) => {
            const distance = map.distance(latLng, point);
            if (distance < nearestDistance) {
                nearestDistance = distance;
                nearestIndex = index;
            }
        });

        for (let index = 1; index <= nearestIndex; index += 1) {
            travelled += map.distance(routeLine[index - 1], routeLine[index]);
        }

        return travelled;
    };

    const updateRouteStats = (latLng) => {
        const total = routeSummary?.totalDistance || map.distance(origin, destination);
        const travelled = Math.min(total, distanceAlongRouteTo(latLng));
        const remaining = Math.max(0, total - travelled);
        const totalTime = routeSummary?.totalTime || 0;
        const etaSeconds = total > 0 && totalTime > 0 ? Math.ceil((remaining / total) * totalTime) : 0;
        const eta = etaSeconds > 0 ? `${Math.max(1, Math.ceil(etaSeconds / 60))} min` : 'Calculating...';

        setText('trackingDistanceTravelled', formatKm(travelled));
        setText('trackingRemainingDistance', formatKm(remaining));
        setText('trackingEta', eta);

        return remaining;
    };

    const moveTruck = (nextPosition, payload) => {
        const startPosition = previousTruckPosition;
        const bearing = bearingBetween(startPosition, nextPosition);
        const startedAt = performance.now();
        const duration = 900;

        const animate = (timestamp) => {
            const progress = Math.min(1, (timestamp - startedAt) / duration);
            const currentPosition = interpolatePoint(startPosition, nextPosition, progress);
            const latLng = L.latLng(currentPosition[0], currentPosition[1]);
            const remaining = updateRouteStats(latLng);

            truckMarker.setLatLng(latLng);
            truckMarker.setIcon(truckIcon(bearing));
            truckMarker.setPopupContent(popupHtml(payload, remaining));

            if (progress < 1) {
                window.requestAnimationFrame(animate);
                return;
            }

            previousTruckPosition = nextPosition;
            map.panTo(latLng, { animate: true, duration: 0.8 });
            truckMarker.openPopup();
        };

        window.requestAnimationFrame(animate);
    };

    const updateTruckFromPayload = (payload) => {
        const nextPosition = normalizePoint([
            payload.currentLatitude,
            payload.currentLongitude,
        ]);

        if (!nextPosition) return;

        latestStatus = payload.status || latestStatus;
        latestStatusClass = payload.statusClass || latestStatusClass;

        if (!isTransitStatus(payload) && pollingTimer) {
            window.clearInterval(pollingTimer);
            pollingTimer = null;
        }

        setText('trackingCurrentLocation', payload.currentLocation || '-');
        setText('trackingCurrentLatitude', Number(nextPosition[0]).toFixed(7));
        setText('trackingCurrentLongitude', Number(nextPosition[1]).toFixed(7));

        moveTruck(nextPosition, {
            ...payload,
            currentLatitude: Number(nextPosition[0]).toFixed(7),
            currentLongitude: Number(nextPosition[1]).toFixed(7),
        });
    };

    const fetchLatestLocation = async () => {
        try {
            const response = await fetch(tracking.locationUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) return;

            updateTruckFromPayload(await response.json());
        } catch (error) {
            console.warn('Unable to refresh cargo location.', error);
        }
    };

    const initialRemaining = updateRouteStats(L.latLng(initialTruckPosition[0], initialTruckPosition[1]));
    truckMarker.bindPopup(popupHtml(tracking, initialRemaining)).openPopup();

    window.setTimeout(() => map.invalidateSize(), 150);

    if (isTransitStatus(tracking)) {
        fetchLatestLocation();
        pollingTimer = window.setInterval(fetchLatestLocation, 5000);
    }
});
</script>
@endif
@endpush
