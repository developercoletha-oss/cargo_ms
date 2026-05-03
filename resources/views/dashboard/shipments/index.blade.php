@extends('layouts.colethaDashboardLayout')

@section('content')
<div class="container-fluid px-3 px-lg-4 py-4 dashboard-page">
    <div class="px-3 px-lg-4 pt-4">
        <section class="dashboard-shared-page-header">
            <div class="dashboard-shared-page-header__content">
                <x-coletha-page-header 
                    title="Shipments Management" 
                    subtitle="Track and manage all cargo shipments {{ $user->country ? 'for ' . strtoupper($user->country) : 'assigned to you' }}" 
                />
            </div>
        </section>
        </div>

    <div class="dashboard-page-body">
        <div class="container-fluid">
            @if($shipments->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    No shipments found{{ $user->country ? ' for ' . strtoupper($user->country) : '' }}.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tracking #</th>
                            <th>Origin</th>
                            <th>Destination</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Assigned To</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shipments as $shipment)
                        <tr>
                            <td>
                                <strong>{{ $shipment->tracking_number }}</strong>
                            </td>
                            <td>
                                {{ $shipment->origin_city ?? $shipment->origin_country }}
                                <br><small class="text-muted">{{ $shipment->origin_country }}</small>
                            </td>
                            <td>
                                {{ $shipment->destination_city ?? $shipment->destination_country }}
                                <br><small class="text-muted">{{ $shipment->destination_country }}</small>
                            </td>
                            <td>
                                @php
                                    $statusClass = match($shipment->status) {
                                        'delivered' => 'success',
                                        'in_transit' => 'primary',
                                        'pending' => 'warning',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $shipment->priority === 'high' ? 'danger' : ($shipment->priority === 'urgent' ? 'dark' : 'secondary') }}">
                                    {{ ucfirst($shipment->priority) }}
                                </span>
                            </td>
                            <td>
                                {{ $shipment->assignedUser?->name ?? 'Unassigned' }}
                            </td>
                            <td>
                                {{ $shipment->created_at->diffForHumans() }}
                            </td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $shipments->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
