@extends('layouts.colethaDashboardLayout')

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">{{ $user->full_name ?: $user->name }}</h5>
                    <div class="text-muted">{{ $user->email }}</div>
                </div>
                <span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <div class="row g-3">
                <div class="col-md-4"><strong>Role:</strong> {{ strtoupper($user->role ?? 'user') }}</div>
                <div class="col-md-4"><strong>Phone:</strong> {{ $user->phone ?: '-' }}</div>
                <div class="col-md-4"><strong>Country:</strong> {{ $user->country ?: '-' }}</div>
                <div class="col-md-6"><strong>Timezone:</strong> {{ $user->timezone ?: '-' }}</div>
                <div class="col-md-6"><strong>Company:</strong> {{ $user->company_name ?: '-' }}</div>
                <div class="col-12"><strong>Address:</strong> {{ $user->address ?: '-' }}</div>
                <div class="col-md-6"><strong>Created:</strong> {{ optional($user->created_at)->format('d M Y, H:i') }}</div>
                <div class="col-md-6"><strong>Updated:</strong> {{ optional($user->updated_at)->format('d M Y, H:i') }}</div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <a href="{{ route('dashboard.users.edit', $user) }}" class="btn btn-primary"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                <a href="{{ route('dashboard.users.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
