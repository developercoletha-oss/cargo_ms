@extends('layouts.app')

@section('title', 'Track Cargo - CFTMS')

@push('critical-head')
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
                                <div class="col-md-6"><strong>Current Location:</strong> {{ $cargo->current_location_city ?: ($cargo->current_location_lat && $cargo->current_location_lng ? 'Live GPS location' : '-') }}</div>
                                <div class="col-md-6"><strong>Location Updated:</strong> {{ optional($cargo->current_location_updated_at)->format('d M Y H:i') ?: '-' }}</div>
                                <div class="col-12"><strong>Description:</strong> {{ $cargo->detail?->description }}</div>
                                <div class="col-12"><strong>Transport Officer:</strong> {{ $cargo->transportStaff?->user?->full_name ?: $cargo->transportStaff?->user?->name ?: 'Not assigned yet' }}</div>
                            </div>
                        </div>
                    </div>

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
