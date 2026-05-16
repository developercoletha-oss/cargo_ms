@extends('layouts.colethaDashboardLayout')

@php
    $isCustomer = $user->role === 'customer';
    $canReview = in_array($user->role, ['admin', 'manager'], true);
    $canAssign = $user->role === 'manager';
@endphp

@section('page_header_actions')
@endsection

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if(in_array($user->role, ['store_keeper', 'admin'], true))
                <div class="alert alert-info py-2 px-3 mb-3">
                    Transporter assignment is manager-only after cargo approval.
                </div>
            @endif

            <form method="GET" action="{{ route('dashboard.cargo.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Search origin/destination...">
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
                                $isOwner = (int) $cargo->customer_id === (int) $user->id;
                                $isPending = $cargo->status === 'pending';
                                $statusClass = $cargo->status === 'approved' ? 'success' : ($cargo->status === 'disapproved' ? 'danger' : 'warning');
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ strtoupper($cargo->origin_country) }} - {{ $cargo->origin_city }}</strong><br>
                                    <small class="text-muted">to {{ strtoupper($cargo->destination_country) }} - {{ $cargo->destination_city }}</small>
                                </td>
                                <td>{{ $cargo->customer?->full_name ?: $cargo->customer?->name }}</td>
                                <td>
                                    <div>{{ $cargo->detail?->description }}</div>
                                    <small class="text-muted">{{ number_format((float) ($cargo->detail?->weight_kg ?? 0), 2) }} kg</small>
                                </td>
                                <td><span class="badge text-bg-{{ $statusClass }}">{{ strtoupper($cargo->status) }}</span></td>
                                <td>{{ $cargo->transportStaff?->user?->full_name ?: $cargo->transportStaff?->user?->name ?: 'Unassigned' }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewCargoModal-{{ $cargo->id }}">
                                        <i class="bi bi-eye me-1"></i>View
                                    </button>
                                    @if($isCustomer && $isOwner && $isPending)
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCargoModal-{{ $cargo->id }}">
                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                        </button>
                                        <form method="POST" action="{{ route('dashboard.cargo.destroy', $cargo) }}" class="d-inline" onsubmit="return confirm('Delete this cargo request?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash me-1"></i>Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No cargo found.</td></tr>
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

@foreach($cargoes as $cargo)
    @php
        $isOwner = (int) $cargo->customer_id === (int) $user->id;
        $isPending = $cargo->status === 'pending';
    @endphp
    <div class="modal fade" id="viewCargoModal-{{ $cargo->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cargo Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
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
                        <div class="col-12"><strong>Status:</strong> {{ strtoupper($cargo->status) }}</div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div class="d-flex gap-2">
                            @if($canReview)
                            @if($cargo->status !== 'approved')
                                <form method="POST" action="{{ route('dashboard.cargo.approve', $cargo) }}">
                                    @csrf
                                    <button class="btn btn-success" type="submit"><i class="bi bi-check-circle me-1"></i>Approve</button>
                                </form>
                            @endif
                            @if($cargo->status !== 'disapproved')
                                <form method="POST" action="{{ route('dashboard.cargo.disapprove', $cargo) }}">
                                    @csrf
                                    <button class="btn btn-outline-danger" type="submit"><i class="bi bi-x-circle me-1"></i>Disapprove</button>
                                </form>
                            @endif
                            @if($canAssign && $cargo->status === 'approved')
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignCargoModal-{{ $cargo->id }}">
                                    <i class="bi bi-person-check me-1"></i>Assign Officer
                                </button>
                            @endif
                        @endif
                    </div>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @if($isCustomer && $isOwner && $isPending)
        <div class="modal fade" id="editCargoModal-{{ $cargo->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('dashboard.cargo.update', $cargo) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Cargo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @include('customer.cargo.partials.form', ['cargo' => $cargo])
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($canAssign && $cargo->status === 'approved')
        <div class="modal fade" id="assignCargoModal-{{ $cargo->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('dashboard.cargo.assign', $cargo) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Assign Transport Officer</h5>
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
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Assign</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection




