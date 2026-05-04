@extends('layouts.colethaDashboardLayout')

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Edit User</h5>
            <form method="POST" action="{{ route('dashboard.users.update', $user) }}">
                @csrf
                @method('PUT')
                @include('dashboard.users._form', ['user' => $user])
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Changes</button>
                    <a href="{{ route('dashboard.users.index') }}" class="btn btn-light"><i class="bi bi-x-circle me-1"></i>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
