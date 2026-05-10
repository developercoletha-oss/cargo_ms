@extends('layouts.auth')

@section('title', 'Register - CFTMS')

@section('content')
    <section class="auth-page auth-login-page">
        <div class="auth-login-frame">
            <div class="auth-login-shell">
                {{-- ── brand panel (identical structure to login) ── --}}
                <aside class="auth-login-brand-panel">
                    <div class="auth-login-brand">
                        <img src="{{ asset('img/MYLOGO.png') }}" alt="CFTMS Logo" class="auth-login-brand-img">
                        <span>
                            <strong>CFTMS</strong>
                            <small>Cargo and Freight Tracking Management System</small>
                        </span>
                    </div>

                    <div class="auth-login-copy">
                        <h1>Start managing your cargo today.</h1>
                        <p>Create a customer account to track shipments, manage logistics and stay connected.</p>
                        <div class="auth-login-guide">
                            <ul>
                                <li>Fill in your personal & company details.</li>
                                <li>Choose a secure password.</li>
                                <li>Account activates after admin approval.</li>
                            </ul>
                        </div>
                    </div>
                </aside>

                {{-- ── form panel (same wrapper classes as login) ── --}}
                <div class="auth-login-form-panel">
                    <div class="auth-login-form-card">
                        <div class="auth-login-title-row">
                            <h2 class="auth-login-title">
                                <i class="bi bi-person-plus"></i>
                                <span>Create Account</span>
                            </h2>
                        </div>

                        <p class="auth-login-subtitle">Register as a customer. Your account will stay pending until admin approval.</p>

                        @include('auth.partials.feedback')

                        <form method="POST" action="{{ route('register.submit') }}" class="auth-form auth-login-form"
                            data-auth-form>
                            @csrf

                            <div class="auth-register-grid">
                                <div class="auth-input-group">
                                    <label for="full_name">Full Name</label>
                                    <div class="auth-input-wrap auth-login-input @error('full_name') is-invalid @enderror">
                                        <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}"
                                            placeholder="John Doe" required autocomplete="name">
                                    </div>
                                    @error('full_name') <div class="auth-field-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="auth-input-group">
                                    <label for="phone">Phone Number</label>
                                    <div class="auth-input-wrap auth-login-input @error('phone') is-invalid @enderror">
                                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}"
                                            placeholder="+2557XXXXXXXX" required autocomplete="tel">
                                    </div>
                                    @error('phone') <div class="auth-field-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="auth-input-group">
                                    <label for="email">Email Address</label>
                                    <div class="auth-input-wrap auth-login-input @error('email') is-invalid @enderror">
                                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                                            placeholder="user@example.test" required autocomplete="email">
                                    </div>
                                    @error('email') <div class="auth-field-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="auth-input-group">
                                    <label for="company_name">Company Name</label>
                                    <div class="auth-input-wrap auth-login-input @error('company_name') is-invalid @enderror">
                                        <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}"
                                            placeholder="ABC Logistics Ltd" required>
                                    </div>
                                    @error('company_name') <div class="auth-field-error">{{ $message }}</div> @enderror
                                </div>

                                <input type="hidden" name="country" value="TZ">

                                <div class="auth-input-group">
                                    <label for="address">Business Address</label>
                                    <div class="auth-input-wrap auth-login-input @error('address') is-invalid @enderror">
                                        <input id="address" type="text" name="address" value="{{ old('address') }}"
                                            placeholder="Street, city, region" required>
                                    </div>
                                    @error('address') <div class="auth-field-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="auth-input-group">
                                    <label for="password">Password</label>
                                    <div class="auth-input-wrap auth-login-input @error('password') is-invalid @enderror">
                                        <input id="password" type="password" name="password"
                                            placeholder="Minimum 8 characters" required autocomplete="new-password">
                                        <button type="button" class="auth-password-toggle" data-password-toggle="password"
                                            aria-label="Toggle password visibility">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    @error('password') <div class="auth-field-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="auth-input-group">
                                    <label for="password_confirmation">Confirm Password</label>
                                    <div class="auth-input-wrap auth-login-input">
                                        <input id="password_confirmation" type="password" name="password_confirmation"
                                            placeholder="Re-type your password" required autocomplete="new-password">
                                        <button type="button" class="auth-password-toggle" data-password-toggle="password_confirmation"
                                            aria-label="Toggle password visibility">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn-brand auth-login-button" data-auth-submit
                                data-loading-text="Creating Account...">
                                <span data-auth-submit-label>Create Account</span>
                            </button>
                        </form>

                        <div class="auth-login-back-wrap">
                            <p class="auth-login-switch-text">
                                Already have an account?
                                <a href="{{ route('login') }}" class="auth-link">Login</a>
                            </p>
                        </div>

                        <div class="auth-login-home-wrap">
                            <a href="{{ route('home') }}" class="auth-login-home-link">
                                <i class="bi bi-arrow-left"></i>
                                <span>Back to Home</span>
                            </a>
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
