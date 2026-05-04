@extends('layouts.colethaDashboardLayout')

@section('page_header_actions')
    <a href="{{ route('dashboard.users.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus me-1"></i> Add User
    </a>
@endsection

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.users.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Search name, email, role...">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i>Search</button>
                    <a href="{{ route('dashboard.users.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Country</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->full_name ?: $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge text-bg-light border">{{ strtoupper($user->role ?? 'user') }}</span></td>
                                <td>{{ $user->country ?: '-' }}</td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge text-bg-success">Active</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('dashboard.users.show', $user) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye me-1"></i>View</a>
                                    <a href="{{ route('dashboard.users.edit', $user) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                                    <form method="POST" action="{{ route('dashboard.users.destroy', $user) }}" class="d-inline"
                                        onsubmit="return confirm('Delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash me-1"></i>Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
