@extends('layouts.colethaDashboardLayout')

@section('page_header_actions')
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-person-plus me-1"></i> Add User
    </button>
@endsection

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3">
    <style>
        .user-view-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem 1.25rem;
        }

        .user-view-item {
            padding: 0.5rem 0.625rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
        }

        .user-view-item.full {
            grid-column: 1 / -1;
        }

        @media (max-width: 767.98px) {
            .user-view-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

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
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewUserModal-{{ $user->id }}"><i class="bi bi-eye me-1"></i>View</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal-{{ $user->id }}"><i class="bi bi-pencil-square me-1"></i>Edit</button>
                                    <form method="POST" action="{{ route('dashboard.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Delete this user?');">
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

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('dashboard.users.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('dashboard.users._form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($users as $user)
    <div class="modal fade" id="viewUserModal-{{ $user->id }}" tabindex="-1" aria-labelledby="viewUserModalLabel-{{ $user->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewUserModalLabel-{{ $user->id }}">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="user-view-grid">
                        <div class="user-view-item"><strong>Name:</strong> {{ $user->full_name ?: $user->name }}</div>
                        <div class="user-view-item"><strong>Email:</strong> {{ $user->email }}</div>
                        <div class="user-view-item"><strong>Role:</strong> {{ strtoupper($user->role ?? 'user') }}</div>
                        <div class="user-view-item"><strong>Phone:</strong> {{ $user->phone ?: '-' }}</div>
                        <div class="user-view-item"><strong>Country:</strong> {{ $user->country ?: '-' }}</div>
                        <div class="user-view-item"><strong>Timezone:</strong> {{ $user->timezone ?: '-' }}</div>
                        <div class="user-view-item"><strong>Company:</strong> {{ $user->company_name ?: '-' }}</div>
                        <div class="user-view-item"><strong>Status:</strong> {{ $user->is_active ? 'Active' : 'Inactive' }}</div>
                        <div class="user-view-item full"><strong>Address:</strong> {{ $user->address ?: '-' }}</div>
                        <div class="user-view-item"><strong>Created:</strong> {{ optional($user->created_at)->format('d M Y, H:i') }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    @if(($user->role === 'customer') && ! $user->is_active)
                        <form method="POST" action="{{ route('dashboard.users.approve', $user) }}" class="me-auto">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-check-circle me-1"></i>Approve Customer
                            </button>
                        </form>
                    @endif
                    @if(($user->role === 'customer') && $user->is_active)
                        <form method="POST" action="{{ route('dashboard.users.deactivate', $user) }}" class="me-auto">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-slash-circle me-1"></i>Deactivate Customer
                            </button>
                        </form>
                    @endif
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editUserModal-{{ $user->id }}" tabindex="-1" aria-labelledby="editUserModalLabel-{{ $user->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('dashboard.users.update', $user) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel-{{ $user->id }}">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('dashboard.users._form', ['user' => $user])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection
