@extends('layouts.auth')

@section('title', 'Register - CFTMS')

@push('critical-head')
    <style>
        .register-page-wrap {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 2rem 0;
        }

        .register-card {
            border-radius: 18px;
            border: 1px solid #bfdbfe;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }

        .register-field {
            border: 2px solid #93c5fd;
            border-radius: 999px;
            min-height: 46px;
            font-size: 0.92rem;
        }

        .register-textarea {
            border-radius: 14px;
            resize: vertical;
            min-height: 46px;
        }
    </style>
@endpush

@section('content')
    <section class="register-page-wrap">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">
                    <div class="card register-card">
                        <div class="card-body p-4 p-md-5">
                            <h2 class="h3 fw-bold text-primary mb-2">
                                <i class="bi bi-person-plus me-2"></i>Create Account
                            </h2>
                            <p class="text-muted mb-4">Register as customer. Account will stay pending until admin approval.</p>

                            @include('auth.partials.feedback')

                            <form method="POST" action="{{ route('register.submit') }}" class="row g-3" data-auth-form>
                                @csrf

                                <div class="col-12 col-md-6">
                                    <label for="full_name" class="form-label fw-semibold">Full Name</label>
                                    <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" class="form-control register-field" placeholder="John Doe" required autocomplete="name">
                                    @error('full_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="form-control register-field" placeholder="+2557XXXXXXXX" required autocomplete="tel">
                                    @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="email" class="form-label fw-semibold">Email Address</label>
                                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control register-field" placeholder="user@example.test" required autocomplete="email">
                                    @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="company_name" class="form-label fw-semibold">Company Name</label>
                                    <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" class="form-control register-field" placeholder="ABC Logistics Ltd" required>
                                    @error('company_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="country" class="form-label fw-semibold">Country Code</label>
                                    <input id="country" type="text" name="country" value="{{ old('country') }}" class="form-control register-field" placeholder="TZ" maxlength="2" required>
                                    @error('country') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="address" class="form-label fw-semibold">Business Address</label>
                                    <textarea id="address" name="address" rows="1" class="form-control register-field register-textarea" placeholder="Street, city, region" required>{{ old('address') }}</textarea>
                                    @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="password" class="form-label fw-semibold">Password</label>
                                    <input id="password" type="password" name="password" class="form-control register-field" placeholder="Minimum 8 characters" required autocomplete="new-password">
                                    @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control register-field" placeholder="Re-type your password" required autocomplete="new-password">
                                </div>

                                <div class="col-12 pt-2">
                                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" data-auth-submit data-loading-text="Creating Account...">
                                        <span data-auth-submit-label>Create Account</span>
                                    </button>
                                </div>
                            </form>

                            <div class="text-center mt-3">
                                <small class="text-muted">Already have an account? <a href="{{ route('login') }}" class="text-decoration-none">Login</a></small>
                            </div>
                            <div class="text-center mt-2">
                                <a href="{{ route('home') }}" class="text-decoration-none fw-semibold"><i class="bi bi-arrow-left me-1"></i>Back to Home</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/colethaAuth.js') }}"></script>
@endsection
