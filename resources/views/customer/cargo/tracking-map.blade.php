@extends('layouts.colethaDashboardLayout')

@php
    $isManagerMap = $mode === 'manager';
    $isTransporterMap = $mode === 'transporter';
    $panelTitle = match ($mode) {
        'manager' => 'Map Items',
        'transporter' => 'Assigned Cargo',
        default => 'My Cargo',
    };
@endphp

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3">
    <div class="cargo-map-workspace">
        <div class="cargo-map-main">
            <section class="card border-0 shadow-sm cargo-map-card">
                <div class="card-body p-0">
                    @if($mapPayload)
                        @if($isManagerMap)
                            <div class="p-3 border-bottom cargo-map-toolbar">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <div>
                                        <div class="text-muted small">Operations Map</div>
                                        <h2 class="h5 fw-normal mb-0">
                                            <span id="managerCargoCount">{{ $mapPayload['stats']['cargoes'] ?? 0 }}</span> cargos,
                                            <span id="managerStoreCount">{{ $mapPayload['stats']['stores'] ?? 0 }}</span> stores
                                        </h2>
                                    </div>
                                    <div class="manager-search input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                        <input id="managerMapSearch" type="search" class="form-control" placeholder="Search cargo or store" autocomplete="off">
                                    </div>
                                </div>
                                <div class="text-muted small mt-2">
                                    Last refresh: <span id="managerLastRefreshed">{{ $mapPayload['stats']['lastRefreshedAt'] ?? '-' }}</span>
                                </div>
                            </div>
                        @endif

                        @if($isTransporterMap)
                            <div class="alert alert-secondary py-2 px-3 m-3 mb-0">
                                <i class="bi bi-truck me-1"></i><span>Simulated cargo movement is using local route coordinates.</span>
                            </div>
                        @endif

                        <div class="cargo-map-wrap">
                            <div id="cargoTrackingMap" class="cargo-tracking-map"></div>
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

        <aside class="cargo-list-dock">
            <section class="card border-0 shadow-sm h-100 cargo-list-panel">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h2 class="h5 fw-normal mb-1">{{ $panelTitle }}</h2>
                            @if($isManagerMap)
                                <p class="text-muted small mb-0"><span id="managerResultCount">0</span> visible</p>
                            @else
                                <p class="text-muted small mb-0">{{ $cargoes->count() }} total</p>
                            @endif
                        </div>
                        <span class="badge text-bg-light border text-secondary">
                            {{ $isManagerMap ? (($mapPayload['stats']['cargoes'] ?? 0) + ($mapPayload['stats']['stores'] ?? 0)) : $cargoes->count() }}
                        </span>
                    </div>

                    @if($isManagerMap)
                        <div id="managerEntityList" class="cargo-list-scroll"></div>
                    @else
                        <div class="list-group list-group-flush cargo-list-scroll">
                            @forelse($cargoes as $cargo)
                                @php
                                    $isSelected = $selectedCargo && (int) $selectedCargo->id === (int) $cargo->id;
                                @endphp
                                <a href="{{ route('dashboard.cargo-map', ['cargo_id' => $cargo->id]) }}"
                                    class="list-group-item list-group-item-action px-0 cargo-list-item {{ $isSelected ? 'is-selected rounded px-3' : '' }}">
                                    <div class="d-flex justify-content-between gap-2">
                                        <span>{{ $cargo->tracking_number }}</span>
                                        <span class="badge text-bg-{{ $cargo->statusBadgeClass() }}">{{ $cargo->statusLabel() }}</span>
                                    </div>
                                    <div class="text-muted small mt-1">
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
                    @endif
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    .cargo-map-workspace {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(280px, 330px);
        gap: 1rem;
        align-items: stretch;
        max-width: 1500px;
        margin: 0 auto;
    }

    .cargo-map-main {
        min-width: 0;
    }

    .cargo-map-card {
        overflow: hidden;
    }

    .cargo-map-toolbar {
        min-height: 96px;
    }

    .cargo-tracking-map {
        height: min(76vh, 760px);
        min-height: 540px;
        width: 100%;
    }

    .cargo-list-dock {
        min-width: 0;
        z-index: 500;
    }

    .cargo-list-panel {
        height: 100%;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
    }

    .cargo-list-panel .card-body {
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .cargo-list-item.is-selected {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
    }

    .cargo-list-item.is-selected:hover,
    .cargo-list-item.is-selected:focus {
        background: #f1f5f9;
        color: #0f172a;
    }

    .cargo-list-scroll {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        padding-right: 0.25rem;
    }

    .cargo-map-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
        background: #f8fafc;
    }

    .manager-search {
        width: min(380px, 100%);
    }

    .tracking-marker {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        color: #ffffff;
        border: 2px solid #ffffff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.25);
    }

    .tracking-marker i {
        font-size: 1.05rem;
        line-height: 1;
    }

    .tracking-marker--pickup {
        background: #0f766e;
    }

    .tracking-marker--destination {
        background: #7c3aed;
    }

    .tracking-marker--cargo {
        background: #b45309;
    }

    .cargo-route-line {
        stroke-dasharray: 8 10;
    }

    .tracking-entity-row {
        border: 0;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 0;
        text-align: left;
    }

    .tracking-entity-row:hover,
    .tracking-entity-row:focus {
        background: #f8fafc;
    }

    .tracking-popup {
        min-width: 250px;
        max-width: 340px;
    }

    .tracking-popup-title {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.35rem;
    }

    .tracking-popup-grid {
        display: grid;
        gap: 0.25rem;
        font-size: 0.82rem;
    }

    .tracking-popup-cargo-list {
        max-height: 220px;
        overflow-y: auto;
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid #e2e8f0;
    }

    @media (max-width: 991.98px) {
        .cargo-map-workspace {
            grid-template-columns: 1fr;
        }

        .cargo-list-dock {
            width: 100%;
            order: 2;
        }

        .cargo-list-scroll {
            max-height: 320px;
        }

        .cargo-tracking-map {
            height: 62vh;
            min-height: 420px;
        }
    }
</style>
@endpush

@push('scripts')
@if($mapPayload)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const mode = @json($mode);
    const overviewUrl = @json($overviewUrl);
    let payload = @json($mapPayload);
    let latestMarkers = new Map();
    let routeLines = new Map();
    let animatedRoutes = new Map();
    let firstFit = true;

    const map = L.map('cargoTrackingMap', {
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

    const hasPosition = (marker) => Array.isArray(marker.position)
        && marker.position.length === 2
        && Number.isFinite(Number(marker.position[0]))
        && Number.isFinite(Number(marker.position[1]));

    const validRouteCoordinates = (marker) => (marker.routeCoordinates || [])
        .filter((position) => Array.isArray(position)
            && position.length === 2
            && Number.isFinite(Number(position[0]))
            && Number.isFinite(Number(position[1])));

    const markerIcon = (marker) => L.divIcon({
        className: '',
        html: `<div class="tracking-marker tracking-marker--${escapeHtml(marker.variant || 'cargo')}"><i class="bi ${escapeHtml(marker.entityType === 'cargo' ? 'bi-truck' : (marker.iconClass || 'bi-geo-alt'))}"></i></div>`,
        iconSize: [34, 34],
        iconAnchor: [17, 17],
        popupAnchor: [0, -18],
    });

    const cargoPopup = (cargo) => `
        <div class="tracking-popup">
            <div class="tracking-popup-title">${escapeHtml(cargo.trackingNumber)}</div>
            <div class="mb-2"><span class="badge text-bg-${escapeHtml(cargo.statusClass)}">${escapeHtml(cargo.status)}</span></div>
            <div class="tracking-popup-grid">
                <div><strong>Cargo:</strong> ${escapeHtml(cargo.description)}</div>
                <div><strong>Customer:</strong> ${escapeHtml(cargo.customerName || '-')}</div>
                <div><strong>Transporter:</strong> ${escapeHtml(cargo.transporterName || '-')}</div>
                <div><strong>Pickup:</strong> ${escapeHtml(cargo.originCity)} / ${escapeHtml(cargo.originAddress)}</div>
                <div><strong>Destination:</strong> ${escapeHtml(cargo.destinationCity)} / ${escapeHtml(cargo.destinationAddress)}</div>
                <div><strong>Current:</strong> ${escapeHtml(cargo.currentLocationLabel)}</div>
                <div><strong>Coordinates:</strong> ${escapeHtml(cargo.currentLatitude)}, ${escapeHtml(cargo.currentLongitude)}</div>
                <div><strong>Updated:</strong> ${escapeHtml(cargo.currentLocationTime)}</div>
                <div><strong>Weight:</strong> ${escapeHtml(cargo.weightKg)} kg</div>
                <div><strong>Quantity:</strong> ${escapeHtml(cargo.quantity)}</div>
                <div><strong>Packages:</strong> ${escapeHtml(cargo.packageCount)}</div>
                <div><strong>Pickup Date:</strong> ${escapeHtml(cargo.pickupDate)}</div>
                <div><strong>Delivery Date:</strong> ${escapeHtml(cargo.deliveryDate)}</div>
                <div><strong>Movements:</strong> ${escapeHtml(cargo.movementCount)}</div>
            </div>
        </div>
    `;

    const storePopup = (marker) => {
        const cargoes = marker.cargoes || [];
        const cargoRows = cargoes.map((cargo) => `
            <div class="py-1">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <strong>${escapeHtml(cargo.trackingNumber)}</strong>
                    <span class="badge text-bg-${escapeHtml(cargo.statusClass)}">${escapeHtml(cargo.status)}</span>
                </div>
                <div class="text-muted">${escapeHtml(cargo.description)}</div>
                <div>${escapeHtml(cargo.route)}</div>
                <div class="text-muted">${escapeHtml(cargo.customerName || '-')}</div>
            </div>
        `).join('');

        return `
            <div class="tracking-popup">
                <div class="tracking-popup-title">${escapeHtml(marker.storeType || 'Store')}</div>
                <div class="tracking-popup-grid">
                    <div><strong>Location:</strong> ${escapeHtml(marker.title)}</div>
                    <div><strong>Address:</strong> ${escapeHtml(marker.subtitle || '-')}</div>
                    <div><strong>Cargo Count:</strong> ${escapeHtml(marker.cargoCount || 0)}</div>
                </div>
                <div class="tracking-popup-cargo-list">${cargoRows || '<div class="text-muted">No cargo linked.</div>'}</div>
            </div>
        `;
    };

    const popupContent = (marker) => marker.entityType === 'store'
        ? storePopup(marker)
        : cargoPopup(marker.cargo);

    const fitVisibleMarkers = (markers) => {
        const points = markers.filter(hasPosition).map((marker) => marker.position);

        if (points.length === 0) {
            map.setView([-6.3690, 34.8888], 6);
            return;
        }

        if (points.length === 1) {
            map.setView(points[0], 9);
            return;
        }

        map.fitBounds(L.latLngBounds(points), {
            padding: [44, 44],
            maxZoom: 9,
        });
    };

    const markerMatchesFilter = (marker) => {
        if (mode !== 'manager') return true;

        const search = document.getElementById('managerMapSearch')?.value.trim().toLowerCase() || '';
        if (search === '') return true;

        return String(marker.searchText || '').toLowerCase().includes(search);
    };

    const visiblePayloadMarkers = () => (payload.markers || [])
        .filter(hasPosition)
        .filter(markerMatchesFilter);

    const renderManagerList = (markers) => {
        const list = document.getElementById('managerEntityList');
        const resultCount = document.getElementById('managerResultCount');

        if (resultCount) resultCount.textContent = markers.length;
        if (!list) return;

        if (markers.length === 0) {
            list.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-search fs-3 d-block mb-2"></i>
                    No matching cargo or store.
                </div>
            `;
            return;
        }

        list.innerHTML = markers.slice(0, 200).map((marker) => {
            const isStore = marker.entityType === 'store';
            const title = isStore ? `${marker.storeType}: ${marker.title}` : marker.cargo.trackingNumber;
            const subtitle = isStore
                ? `${marker.cargoCount || 0} cargo item(s)`
                : `${marker.cargo.status} / ${marker.cargo.currentLocationLabel}`;
            const icon = isStore ? 'bi-shop' : 'bi-box-seam';

            return `
                <button type="button" class="list-group-item list-group-item-action tracking-entity-row px-0 py-3" data-marker-key="${escapeHtml(marker.key)}">
                    <div class="d-flex align-items-start gap-2">
                        <span class="badge text-bg-light border text-secondary"><i class="bi ${icon}"></i></span>
                        <span class="d-block">
                            <span class="d-block fw-semibold">${escapeHtml(title)}</span>
                            <span class="d-block text-muted small">${escapeHtml(subtitle)}</span>
                        </span>
                    </div>
                </button>
            `;
        }).join('');

        list.querySelectorAll('[data-marker-key]').forEach((row) => {
            row.addEventListener('click', () => {
                const marker = latestMarkers.get(row.dataset.markerKey);
                if (!marker) return;

                map.panTo(marker.getLatLng());
                marker.openPopup();
            });
        });
    };

    const renderMarkers = (fitMap = false) => {
        const visibleMarkers = visiblePayloadMarkers();
        const nextKeys = new Set();
        const nextRouteKeys = new Set();

        visibleMarkers.forEach((markerData) => {
            nextKeys.add(markerData.key);
            const routeCoordinates = validRouteCoordinates(markerData);

            if (latestMarkers.has(markerData.key)) {
                const marker = latestMarkers.get(markerData.key);
                marker.setIcon(markerIcon(markerData));
                marker.setPopupContent(popupContent(markerData));
                animateCargoMarker(markerData, marker);
            } else {
                const marker = L.marker(markerData.position, {
                    icon: markerIcon(markerData),
                    title: markerData.title || '',
                }).addTo(map);

                marker.bindPopup(popupContent(markerData));
                latestMarkers.set(markerData.key, marker);
                animateCargoMarker(markerData, marker);
            }

            if (markerData.entityType !== 'cargo' || routeCoordinates.length < 2) return;

            nextRouteKeys.add(markerData.key);

            if (routeLines.has(markerData.key)) {
                routeLines.get(markerData.key).setLatLngs(routeCoordinates);
            } else {
                routeLines.set(markerData.key, L.polyline(routeCoordinates, {
                    className: 'cargo-route-line',
                    color: '#2563eb',
                    opacity: 0.7,
                    weight: 4,
                }).addTo(map));
            }
        });

        latestMarkers.forEach((marker, key) => {
            if (nextKeys.has(key)) return;
            map.removeLayer(marker);
            latestMarkers.delete(key);
            animatedRoutes.delete(key);
        });

        routeLines.forEach((routeLine, key) => {
            if (nextRouteKeys.has(key)) return;
            map.removeLayer(routeLine);
            routeLines.delete(key);
        });

        if (mode === 'manager') {
            renderManagerList(visibleMarkers);
        }

        if (fitMap || firstFit) {
            fitVisibleMarkers(visibleMarkers);
            firstFit = false;
        }
    };

    const interpolatePosition = (from, to, progress) => [
        Number(from[0]) + ((Number(to[0]) - Number(from[0])) * progress),
        Number(from[1]) + ((Number(to[1]) - Number(from[1])) * progress),
    ];

    const routePositionAt = (routeCoordinates, routeProgress) => {
        if (routeCoordinates.length === 0) return null;
        if (routeCoordinates.length === 1) return routeCoordinates[0];

        const scaledProgress = routeProgress * (routeCoordinates.length - 1);
        const fromIndex = Math.floor(scaledProgress);
        const toIndex = Math.min(fromIndex + 1, routeCoordinates.length - 1);

        return interpolatePosition(
            routeCoordinates[fromIndex],
            routeCoordinates[toIndex],
            scaledProgress - fromIndex
        );
    };

    const animateCargoMarker = (markerData, marker) => {
        const routeCoordinates = validRouteCoordinates(markerData);

        if (markerData.entityType !== 'cargo' || routeCoordinates.length < 2) {
            marker.setLatLng(markerData.position);
            return;
        }

        const existing = animatedRoutes.get(markerData.key);
        const signature = JSON.stringify(routeCoordinates);

        if (existing?.signature === signature) return;

        animatedRoutes.set(markerData.key, {
            signature,
            startedAt: performance.now(),
        });

        const duration = Math.max(8000, routeCoordinates.length * (payload.animationIntervalMs || 900));

        const step = (timestamp) => {
            const routeState = animatedRoutes.get(markerData.key);
            if (!routeState || routeState.signature !== signature) return;

            const elapsed = (timestamp - routeState.startedAt) % duration;
            const position = routePositionAt(routeCoordinates, elapsed / duration);

            if (position) marker.setLatLng(position);
            window.requestAnimationFrame(step);
        };

        window.requestAnimationFrame(step);
    };

    const updateDetailHeader = () => {
        if (!payload.cargo) return;

        const cargo = payload.cargo;
        const setText = (id, value) => {
            const node = document.getElementById(id);
            if (node) node.textContent = value ?? '-';
        };

        setText('detailTrackingNumber', cargo.trackingNumber);
        setText('detailOrigin', cargo.originCity);
        setText('detailDestination', cargo.destinationCity);
        setText('detailCargoDescription', cargo.description);
        setText('detailTransporter', cargo.transporterName);
        setText('currentLocationLabel', payload.currentLocationLabel || cargo.currentLocationLabel);
        setText('currentLocationTime', payload.currentLocationTime || cargo.currentLocationTime || '-');
        setText('movementCount', payload.movementCount ?? cargo.movementCount ?? 0);

        const badge = document.getElementById('detailStatusBadge');
        if (badge) {
            badge.className = `badge text-bg-${payload.statusClass || cargo.statusClass} fs-6`;
            badge.textContent = payload.status || cargo.status;
        }
    };

    const updateManagerStats = () => {
        if (mode !== 'manager' || !payload.stats) return;

        const setText = (id, value) => {
            const node = document.getElementById(id);
            if (node) node.textContent = value ?? '-';
        };

        setText('managerCargoCount', payload.stats.cargoes);
        setText('managerStoreCount', payload.stats.stores);
        setText('managerLastRefreshed', payload.stats.lastRefreshedAt);
    };

    const refreshDetail = async () => {
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

        payload = await response.json();
        updateDetailHeader();
        renderMarkers(false);
    };

    const refreshOverview = async () => {
        if (!overviewUrl) return;

        const response = await fetch(overviewUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            cache: 'no-store',
        });

        if (!response.ok) return;

        payload = await response.json();
        updateManagerStats();
        renderMarkers(false);
    };

    renderMarkers(true);
    updateDetailHeader();
    updateManagerStats();

    window.setTimeout(() => {
        map.invalidateSize();
        renderMarkers(true);
    }, 150);

    if (mode === 'manager') {
        document.getElementById('managerMapSearch')?.addEventListener('input', () => {
            renderMarkers(true);
        });

        window.setInterval(() => {
            refreshOverview().catch(() => {});
        }, 3000);
    } else {
        window.setInterval(() => {
            refreshDetail().catch(() => {});
        }, 3000);
    }

});
</script>
@endif
@endpush
