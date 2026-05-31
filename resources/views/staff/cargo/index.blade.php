@extends('layouts.colethaDashboardLayout')

@php
    $canReview = in_array($user->role, ['admin', 'manager'], true);
    $canAssign = $user->role === 'manager';
    $canRegisterCargo = $user->role === 'store_keeper';
    $isTransporter = $user->role === 'transporter';
    $liveTrackingCargoes = $isTransporter
        ? $cargoes->getCollection()
            ->filter(fn ($cargo) => $cargo->status === 'in_transit' && $cargo->transportStaff && (int) $cargo->transportStaff->user_id === (int) $user->id)
            ->map(fn ($cargo) => [
                'id' => $cargo->id,
                'tracking_number' => $cargo->tracking_number,
                'endpoint' => route('dashboard.cargo.live-location', $cargo),
            ])
            ->values()
        : collect();
@endphp

@section('page_header_actions')
    @if($canRegisterCargo)
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#registerCargoModal">
            <i class="bi bi-plus-circle me-1"></i> Register Cargo
        </button>
    @endif
@endsection

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($liveTrackingCargoes->isNotEmpty())
                <div class="alert alert-info py-2 px-3 mb-3" id="liveGpsStatus">
                    <i class="bi bi-broadcast-pin me-1"></i>
                    Live GPS tracking is starting. Allow location access when your browser asks.
                </div>
            @endif

            <form method="GET" action="{{ route('dashboard.cargo.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Search tracking number/origin/destination...">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i>Search</button>
                    <a href="{{ route('dashboard.cargo.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Tracking No.</th>
                            <th>Route</th>
                            <th>Customer</th>
                            <th>Cargo</th>
                            <th>Status</th>
                            <th>Assigned Officer</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cargoes as $cargo)
                            @php
                                $statusClass = $cargo->statusBadgeClass();
                            @endphp
                            <tr>
                                <td><strong>{{ $cargo->tracking_number }}</strong></td>
                                <td>
                                    <strong>{{ strtoupper($cargo->origin_country) }} - {{ $cargo->origin_city }}</strong><br>
                                    <small class="text-muted">to {{ strtoupper($cargo->destination_country) }} - {{ $cargo->destination_city }}</small>
                                </td>
                                <td>{{ $cargo->customer?->full_name ?: $cargo->customer?->name }}</td>
                                <td>
                                    <div>{{ $cargo->detail?->description }}</div>
                                    <small class="text-muted">{{ number_format((float) ($cargo->detail?->weight_kg ?? 0), 2) }} kg</small>
                                </td>
                                <td><span class="badge text-bg-{{ $statusClass }}">{{ $cargo->statusLabel() }}</span></td>
                                <td>{{ $cargo->transportStaff?->user?->full_name ?: $cargo->transportStaff?->user?->name ?: 'Unassigned' }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewCargoModal-{{ $cargo->id }}">
                                        <i class="bi bi-eye me-1"></i>View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No cargo found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $cargoes->links() }}
            </div>
        </div>
    </div>
</div>

@if($canRegisterCargo)
    <div class="modal fade" id="registerCargoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('dashboard.cargo.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Register Cargo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">Select customer...</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->full_name ?: $customer->name }} ({{ $customer->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @include('customer.cargo.partials.form')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Cargo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@foreach($cargoes as $cargo)
    <div class="modal fade" id="viewCargoModal-{{ $cargo->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cargo Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Tracking No.:</strong> {{ $cargo->tracking_number }}</div>
                        <div class="col-md-6"><strong>From:</strong> {{ strtoupper($cargo->origin_country) }} / {{ $cargo->origin_city }}</div>
                        <div class="col-md-6"><strong>To:</strong> {{ strtoupper($cargo->destination_country) }} / {{ $cargo->destination_city }}</div>
                        <div class="col-md-6"><strong>Pickup Date:</strong> {{ optional($cargo->pickup_date)->format('d M Y') ?: '-' }}</div>
                        <div class="col-md-6"><strong>Delivery Date:</strong> {{ optional($cargo->delivery_date)->format('d M Y') ?: '-' }}</div>
                        <div class="col-md-6"><strong>Weight:</strong> {{ number_format((float) ($cargo->detail?->weight_kg ?? 0), 2) }} kg</div>
                        <div class="col-md-6"><strong>Quantity:</strong> {{ $cargo->detail?->quantity ?? '-' }}</div>
                        <div class="col-md-6"><strong>Packages:</strong> {{ $cargo->detail?->package_count ?? '-' }}</div>
                        <div class="col-md-6"><strong>Value:</strong> {{ $cargo->detail?->estimated_value ?? '-' }}</div>
                        <div class="col-12"><strong>Description:</strong> {{ $cargo->detail?->description }}</div>
                        <div class="col-12"><strong>Special Instructions:</strong> {{ $cargo->detail?->special_instructions ?: '-' }}</div>
                        <div class="col-12"><strong>Status:</strong> {{ $cargo->statusLabel() }}</div>
                        <div class="col-md-6"><strong>Current Location:</strong> {{ $cargo->current_location_city ?: ($cargo->current_location_lat && $cargo->current_location_lng ? 'Live GPS location' : '-') }}</div>
                        <div class="col-md-6"><strong>Location Updated:</strong> {{ optional($cargo->current_location_updated_at)->format('d M Y H:i') ?: '-' }}</div>
                        <div class="col-12">
                            <strong>Transporter Sign:</strong>
                            @if($cargo->signed_at)
                                Signed on {{ optional($cargo->signed_at)->format('d M Y H:i') }}
                            @else
                                Not signed
                            @endif
                        </div>
                        <div class="col-12">
                            <strong>Store Keeper Handover Confirm:</strong>
                            @if($cargo->handover_confirmed_at)
                                Confirmed on {{ optional($cargo->handover_confirmed_at)->format('d M Y H:i') }}
                            @else
                                Not confirmed
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div class="d-flex gap-2">
                            @if($canReview)
                            @if($cargo->status === 'pending')
                                <form method="POST" action="{{ route('dashboard.cargo.approve', $cargo) }}">
                                    @csrf
                                    <button class="btn btn-success" type="submit"><i class="bi bi-check-circle me-1"></i>Approve</button>
                                </form>
                            @endif
                            @if($cargo->status === 'pending')
                                <form method="POST" action="{{ route('dashboard.cargo.disapprove', $cargo) }}">
                                    @csrf
                                    <button class="btn btn-outline-danger" type="submit"><i class="bi bi-x-circle me-1"></i>Disapprove</button>
                                </form>
                            @endif
                            @if($canAssign && $cargo->status === 'approved')
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignCargoModal-{{ $cargo->id }}">
                                    <i class="bi bi-person-check me-1"></i>Assign / Update Schedule
                                </button>
                            @endif
                        @endif
                        @if($isTransporter && $cargo->status === 'approved' && $cargo->transportStaff && (int) $cargo->transportStaff->user_id === (int) $user->id && ! $cargo->signed_at)
                            <form method="POST" action="{{ route('dashboard.cargo.sign', $cargo) }}">
                                @csrf
                                <button class="btn btn-primary" type="submit"><i class="bi bi-pen me-1"></i>Sign Cargo</button>
                            </form>
                        @endif
                        @if($isTransporter && $cargo->status === 'in_transit' && $cargo->transportStaff && (int) $cargo->transportStaff->user_id === (int) $user->id)
                            <form method="POST" action="{{ route('dashboard.cargo.mark-arrived', $cargo) }}">
                                @csrf
                                <button class="btn btn-info" type="submit"><i class="bi bi-geo-alt me-1"></i>Mark Arrived</button>
                            </form>
                        @endif
                        @if($isTransporter && $cargo->status === 'arrived' && $cargo->transportStaff && (int) $cargo->transportStaff->user_id === (int) $user->id)
                            <form method="POST" action="{{ route('dashboard.cargo.mark-delivered', $cargo) }}">
                                @csrf
                                <button class="btn btn-success" type="submit"><i class="bi bi-check2-circle me-1"></i>Mark Delivered</button>
                            </form>
                        @endif
                        @if($canRegisterCargo && $cargo->status === 'approved' && $cargo->signed_at && ! $cargo->handover_confirmed_at)
                            <form method="POST" action="{{ route('dashboard.cargo.confirm-handover', $cargo) }}">
                                @csrf
                                <button class="btn btn-outline-success" type="submit"><i class="bi bi-check2-square me-1"></i>Confirm Handover</button>
                            </form>
                        @endif
                    </div>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @if($canAssign && $cargo->status === 'approved')
        <div class="modal fade" id="assignCargoModal-{{ $cargo->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('dashboard.cargo.assign', $cargo) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Assign Transport Officer & Schedule</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label" for="transport_staff_id_{{ $cargo->id }}">Transport Officer</label>
                            <select id="transport_staff_id_{{ $cargo->id }}" name="transport_staff_id" class="form-select" required>
                                <option value="">Select officer...</option>
                                @foreach($transportStaff as $staff)
                                    <option value="{{ $staff->id }}" @selected((int) $cargo->transport_staff_id === (int) $staff->id)>
                                        {{ $staff->staff_code }} - {{ $staff->user?->full_name ?: $staff->user?->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="mt-3">
                                <label class="form-label" for="pickup_date_{{ $cargo->id }}">Pickup Date</label>
                                <input id="pickup_date_{{ $cargo->id }}" type="date" name="pickup_date" class="form-control"
                                    value="{{ old('pickup_date', optional($cargo->pickup_date)->format('Y-m-d')) }}">
                            </div>
                            <div class="mt-3">
                                <label class="form-label" for="delivery_date_{{ $cargo->id }}">Delivery Date</label>
                                <input id="delivery_date_{{ $cargo->id }}" type="date" name="delivery_date" class="form-control"
                                    value="{{ old('delivery_date', optional($cargo->delivery_date)->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Assignment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection

@push('scripts')
@if($liveTrackingCargoes->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', () => {
    const liveCargoes = @json($liveTrackingCargoes);
    const statusEl = document.getElementById('liveGpsStatus');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let lastSentAt = 0;

    const setStatus = (message, tone = 'info') => {
        if (!statusEl) return;
        statusEl.className = `alert alert-${tone} py-2 px-3 mb-3`;
        statusEl.innerHTML = `<i class="bi bi-broadcast-pin me-1"></i>${message}`;
    };

    if (!navigator.geolocation) {
        setStatus('This browser does not support live GPS tracking.', 'warning');
        return;
    }

    const sendLocation = async (position) => {
        const now = Date.now();
        if (now - lastSentAt < 15000) return;
        lastSentAt = now;

        const payload = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
        };

        await Promise.all(liveCargoes.map((cargo) => fetch(cargo.endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        })));

        setStatus(`Live GPS is active for ${liveCargoes.length} in-transit cargo item(s). Last sent: ${new Date().toLocaleTimeString()}`, 'success');
    };

    navigator.geolocation.watchPosition(
        (position) => {
            sendLocation(position).catch(() => {
                setStatus('Live GPS is available, but the latest location could not be sent.', 'warning');
            });
        },
        () => {
            setStatus('Location access is required for live cargo tracking.', 'warning');
        },
        {
            enableHighAccuracy: true,
            maximumAge: 10000,
            timeout: 20000,
        }
    );
});
</script>
@endif
@endpush
