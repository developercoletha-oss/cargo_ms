@extends('layouts.colethaDashboardLayout')

@section('content')
<div class="container-fluid px-3 px-lg-4 py-4 dashboard-page">
    <div class="row g-3 g-xl-4 mb-4">
        @foreach ($stats as $stat)
            <div class="col-6 col-xxl-3">
                <article class="dashboard-stat-card dashboard-stat-card--{{ $stat['tone'] }}">
                    <div class="dashboard-stat-card__icon">
                        <i class="bi {{ $stat['icon'] }}"></i>
                    </div>
                    <div>
                        <span class="dashboard-stat-card__label">{{ $stat['label'] }}</span>
                        <h2 class="dashboard-stat-card__value">{{ is_numeric($stat['value']) ? number_format($stat['value']) : $stat['value'] }}</h2>
                    </div>
                </article>
            </div>
        @endforeach
    </div>

    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12">
            <section class="dashboard-panel h-100">
                <div class="dashboard-panel__header">
                    <div>
                        <span class="dashboard-panel__eyebrow">Quick Summary</span>
                         <h3 class="dashboard-panel__title">
                                System Overview
                         </h3>
                    </div>
                    @if(isset($user) && $user->country && in_array($user->role, ['admin','hgadmin','manager','staff']))
                    <div class="d-flex gap-2 align-items-center">
                        <a href="{{ route('dashboard.shipments.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-eye"></i> View All Shipments
                        </a>
                    </div>
                    @endif
                </div>

                <div class="row g-3">
                    @foreach ($summaryTiles as $tile)
                        <div class="col-6 col-xl-3">
                            <article class="dashboard-summary-tile">
                                <div class="dashboard-summary-tile__icon" style="flex-shrink: 0;">
                                    <i class="bi {{ $tile['icon'] }}"></i>
                                </div>
                                <div>
                                    <span class="dashboard-summary-tile__label">{{ $tile['label'] }}</span>
                                    <div class="dashboard-summary-tile__value">{{ $tile['value'] }}</div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>

    @if(isset($recentShipments) && $recentShipments->isNotEmpty())
    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12">
            <section class="dashboard-panel">
                <div class="dashboard-panel__header">
                    <div>
                        <span class="dashboard-panel__eyebrow">Recent Activity</span>
                        <h3 class="dashboard-panel__title">Latest Shipments</h3>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <a href="{{ route('dashboard.shipments.index') }}" class="btn btn-outline-secondary btn-sm">
                            View All
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tracking #</th>
                                <th>Route</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Created</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentShipments as $shipment)
                            <tr>
                                <td>
                                    <strong>{{ $shipment->tracking_number }}</strong>
                                </td>
                                <td>
                                    {{ $shipment->origin_country }} → {{ $shipment->destination_country }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border">{{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</span>
                                </td>
                                <td>{{ $shipment->assignedUser?->name ?? 'Unassigned' }}</td>
                                <td>{{ $shipment->created_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('dashboard.shipments.index') }}?search={{ $shipment->tracking_number }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-search"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
    @endif

    <div class="row g-3 g-xl-4">
        <div class="col-lg-8">
            <section class="dashboard-panel h-100">
                <div class="dashboard-panel__header">
                    <div>
                        <span class="dashboard-panel__eyebrow">Monthly Trend</span>
                        <h3 class="dashboard-panel__title">Applications Overview</h3>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="dashboard-chart-badge dashboard-chart-badge--up">
                            <i class="bi bi-arrow-up-right"></i> 12.4%
                        </span>
                        <span class="text-muted" style="font-size:0.78rem;">vs last year</span>
                    </div>
                </div>
                <div style="position: relative; height: 280px;">
                    <canvas id="applicationsChart"></canvas>
                </div>
            </section>
        </div>
        <div class="col-lg-4">
            <section class="dashboard-panel h-100">
                 <h4 class="fw-semibold mb-4 dashboard-activity-title">Recent Activity</h4>
                 <div class="list-group list-group-flush">
                    <div class="list-group-item px-0 border-0 mb-3">
                        <div class="d-flex gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 h-100">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">New user registered</h6>
                                <small class="text-muted">2 minutes ago</small>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item px-0 border-0 mb-3">
                        <div class="d-flex gap-3">
                            <div class="bg-success bg-opacity-10 text-success p-2 rounded-3 h-100">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Security scan completed</h6>
                                <small class="text-muted">1 hour ago</small>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item px-0 border-0">
                        <div class="d-flex gap-3">
                            <div class="bg-info bg-opacity-10 text-info p-2 rounded-3 h-100">
                                <i class="bi bi-cloud-upload"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">System backup successful</h6>
                                <small class="text-muted">3 hours ago</small>
                            </div>
                        </div>
                    </div>
                 </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('applicationsChart');
    if (!ctx) return;

    const primaryColor = getComputedStyle(document.documentElement)
        .getPropertyValue('--color-primary-600').trim() || '#2563eb';

    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const data   = [38, 52, 61, 45, 78, 95, 88, 102, 74, 115, 130, 142];

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Applications',
                data,
                fill: true,
                tension: 0.45,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 2.5,
                borderColor: '#2563eb',
                pointBackgroundColor: '#2563eb',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                backgroundColor: function(context) {
                    const chart = context.chart;
                    const { ctx: c, chartArea } = chart;
                    if (!chartArea) return 'rgba(37,99,235,0.08)';
                    const gradient = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, 'rgba(37,99,235,0.18)');
                    gradient.addColorStop(1, 'rgba(37,99,235,0.0)');
                    return gradient;
                }
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#94a3b8',
                    bodyColor: '#f1f5f9',
                    padding: 12,
                    cornerRadius: 10,
                    displayColors: false,
                    callbacks: {
                        title: (items) => items[0].label,
                        label: (item) => ` ${item.formattedValue} Applications`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 12 } }
                },
                y: {
                    beginAtZero: true,
                    border: { display: false, dash: [4, 4] },
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { color: '#94a3b8', font: { size: 12 }, maxTicksLimit: 6 }
                }
            }
        }
    });
});
</script>
@endpush
