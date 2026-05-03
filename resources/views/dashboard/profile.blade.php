@extends('layouts.colethaDashboardLayout')

@section('content')
<div class="container-fluid px-3 px-lg-4 py-4 dashboard-profile-page">
    <div class="row g-4">
        <div class="col-12 col-xl-4">
            <section class="profile-summary-card h-100">
                <span class="profile-status-pill profile-status-pill-top">
                    <i class="bi bi-shield-check"></i>
                    System User
                </span>

                <div class="profile-summary-top">
                    <div class="profile-avatar-shell">
                        <div class="profile-avatar-fallback">
                            <i class="bi bi-person profile-avatar-icon" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="profile-name">{{ $user->full_name }}</h2>
                        <p class="profile-email">{{ $user->email }}</p>
                    </div>
                </div>

                <button type="button" class="btn btn-primary profile-edit-btn" data-bs-toggle="modal" data-bs-target="#profileUpdateModal">
                    <i class="bi bi-pencil-square me-2"></i>Edit Profile
                </button>
            </section>
        </div>

        <div class="col-12 col-xl-8">
            <section class="profile-details-card">
                <div class="profile-section-head">
                    <div>
                        <div class="profile-section-kicker">Account Overview</div>
                        <h3 class="profile-section-title fw-semibold">Personal Information</h3>
                    </div>
                </div>

                <div class="row g-3 profile-info-grid">
                    <div class="col-md-6">
                        <article class="profile-info-card">
                            <span class="profile-info-label">Full Name</span>
                            <span class="profile-info-value">{{ $user->full_name }}</span>
                        </article>
                    </div>
                    <div class="col-md-6">
                        <article class="profile-info-card">
                            <span class="profile-info-label">Email Address</span>
                            <span class="profile-info-value">{{ $user->email }}</span>
                        </article>
                    </div>
                    <div class="col-md-6">
                        <article class="profile-info-card">
                            <span class="profile-info-label">Last Login</span>
                            <span class="profile-info-value">
                                {{ optional($user->last_login_at)->format('d M Y, H:i') ?? 'Not recorded yet' }}
                            </span>
                        </article>
                    </div>
                    <div class="col-md-6">
                        <article class="profile-info-card">
                            <span class="profile-info-label">Account Created</span>
                            <span class="profile-info-value">{{ optional($user->created_at)->format('d M Y') ?? 'Unknown' }}</span>
                        </article>
                    </div>
                </div>
            </section>
        </div>
    </div>
    
    <div class="modal fade" id="profileUpdateModal" tabindex="-1" aria-labelledby="profileUpdateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content profile-modal">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <div class="profile-section-kicker">Edit Profile</div>
                        <h4 class="modal-title profile-modal-title fw-semibold" id="profileUpdateModalLabel">Update your details</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form method="POST" action="{{ route('dashboard.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="modal-body pt-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input
                                    type="text"
                                    id="full_name"
                                    name="full_name"
                                    class="form-control @error('full_name') is-invalid @enderror"
                                    value="{{ old('full_name', $user->full_name) }}"
                                    required
                                >
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}"
                                    required
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">New Password</label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Leave blank to keep current"
                                >
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="form-control"
                                    placeholder="Repeat the new password"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" data-loading-text="Saving changes...">
                            <i class="bi bi-check2-circle me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .dashboard-profile-page {
        color: var(--color-dark-800);
        font-size: 0.95rem;
    }

    .profile-summary-card,
    .profile-details-card {
        background: var(--color-white);
        border: 1px solid rgba(var(--color-dark-800-rgb), 0.12);
        border-radius: 24px;
        padding: 1.6rem;
        box-shadow: 0 16px 40px rgba(var(--color-dark-900-rgb), 0.08);
    }

    .profile-summary-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1.25rem;
        min-height: 100%;
        background: linear-gradient(180deg, rgba(var(--color-primary-500-rgb), 0.08) 0%, var(--color-white) 100%);
    }

    .profile-summary-top {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
    }

    .profile-avatar-fallback {
        width: 84px;
        height: 84px;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--color-primary-600), var(--color-primary-700));
        border: 2px solid rgba(255, 255, 255, 0.92);
        color: var(--color-white);
        box-shadow: 0 10px 24px rgba(var(--color-dark-900-rgb), 0.22);
    }

    .profile-avatar-icon {
        font-size: 2rem;
    }

    .profile-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.35rem 0.8rem;
        border-radius: 999px;
        background: rgba(var(--color-primary-500-rgb), 0.1);
        color: var(--color-primary-700);
        font-size: 0.72rem;
        margin-bottom: 0.75rem;
        border: 1px solid rgba(var(--color-primary-500-rgb), 0.16);
    }

    .profile-name {
        margin: 0;
        font-size: clamp(1rem, 1.2vw, 1.18rem);
        font-weight: 600;
    }

    .profile-email {
        margin: 0.35rem 0 0;
        color: var(--color-slate-500);
        font-size: 0.88rem;
    }

    .profile-info-card {
        background: rgba(var(--color-primary-500-rgb), 0.05);
        border: 1px solid rgba(var(--color-primary-500-rgb), 0.12);
        border-radius: 18px;
        padding: 1rem 1.05rem;
    }

    .profile-info-label {
        display: block;
        font-size: 0.7rem;
        text-transform: uppercase;
        color: var(--color-slate-500);
        margin-bottom: 0.4rem;
    }

    .profile-info-value {
        color: var(--color-dark-800);
        font-size: 0.84rem;
        font-weight: 500;
    }

    .profile-edit-btn {
        background: var(--color-primary-600);
        border-color: var(--color-primary-600);
        border-radius: 16px;
        padding: 0.7rem 0.95rem;
        width: 100%;
    }

    .profile-edit-btn:hover {
        background: var(--color-primary-700);
        border-color: var(--color-primary-700);
    }

    .profile-section-kicker {
        font-size: 0.68rem;
        text-transform: uppercase;
        color: var(--color-primary-700);
        margin-bottom: 0.35rem;
    }

    .profile-section-title {
        margin: 0;
        font-weight: 700;
        font-size: clamp(1rem, 1.2vw, 1.12rem);
    }

    .profile-modal-title {
        font-size: clamp(1rem, 1.15vw, 1.12rem);
        color: var(--color-dark-800);
    }

    @media (max-width: 768px) {
        .profile-summary-card,
        .profile-details-card {
            border-radius: 20px;
            padding: 1.2rem;
        }

        .profile-avatar-fallback {
            width: 72px;
            height: 72px;
            border-radius: 20px;
        }
    }

    .profile-modal {
        border-radius: 24px;
        overflow: hidden;
    }

    .profile-modal .form-control {
        border-radius: 12px;
    }
</style>
@endpush
