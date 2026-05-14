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
                    <tbody id="shipmentsTableBody">
                        @include('staff.shipments.partials.rows', ['shipments' => $shipments])
                    </tbody>
                </table>
            </div>

            <div id="shipmentsLoadingState" class="text-center py-3 {{ $shipments->hasMorePages() ? '' : 'd-none' }}">
                <small class="text-muted">Loading more shipments...</small>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('shipmentsTableBody');
    const loadingState = document.getElementById('shipmentsLoadingState');
    if (!tbody || !loadingState) return;

    let nextPage = {{ $shipments->currentPage() + 1 }};
    let hasMore = {{ $shipments->hasMorePages() ? 'true' : 'false' }};
    let loading = false;

    const loadMore = async () => {
        if (!hasMore || loading) return;
        loading = true;

        try {
            const url = new URL("{{ route('dashboard.shipments.index') }}", window.location.origin);
            url.searchParams.set('page', String(nextPage));

            const response = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to load shipments');
            const data = await response.json();

            if (data.rows) {
                tbody.insertAdjacentHTML('beforeend', data.rows);
            }

            hasMore = Boolean(data.has_more);
            nextPage = Number(data.next_page || (nextPage + 1));
            if (!hasMore) loadingState.classList.add('d-none');
        } catch (error) {
            loadingState.innerHTML = '<small class="text-muted">Unable to load more shipments.</small>';
        } finally {
            loading = false;
        }
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) loadMore();
        });
    }, { rootMargin: '180px' });

    if (hasMore) {
        observer.observe(loadingState);
    } else {
        loadingState.classList.add('d-none');
    }
});
</script>
@endpush
