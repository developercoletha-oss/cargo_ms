@extends('layouts.auth')

@section('title', 'Login - CFTMS')

@section('content')
    <section class="auth-page auth-login-page">
        <div class="auth-login-frame">
            <div class="auth-login-shell">
                <aside class="auth-login-brand-panel">
                    <div class="auth-login-brand">
                        <img src="{{ asset('img/MYLOGO.png') }}" alt="CFTMS Logo" class="auth-login-brand-img">
                        <span>
                            <strong>CFTMS</strong>
                            <small>Cargo and Freight Tracking Management System</small>
                        </span>
                    </div>

                    <div class="auth-login-copy">
                        <h1>Welcome back to your cargo operations.</h1>
                        <p class="auth-login-description">
                            Sign in to manage shipments and keep your logistics in one place.
                        </p>
                        <div class="auth-login-guide">
                            <ul>
                                <li>Enter your email address.</li>
                                <li>Type your password.</li>
                                <li>Click Login to continue.</li>
                            </ul>
                        </div>
                    </div>
                </aside>

                <div class="auth-login-form-panel">
                    <div class="auth-login-form-card">
                        <div class="auth-login-title-row">
                            <h2 class="auth-login-title">
                                <i class="bi bi-box-seal"></i>
                                <span>Login</span>
                            </h2>
                        </div>

                        <p class="auth-login-subtitle">Enter your credentials to access the cargo management system.</p>

                        @include('auth.partials.feedback')

                        <form method="POST" action="{{ route('login.submit') }}" class="auth-form auth-login-form"
                            data-auth-form>
                            @csrf

                            <div class="auth-input-group">
                                <label for="email">Email address</label>
                                <div class="auth-input-wrap auth-login-input @error('email') is-invalid @enderror">
                                    <input id="email" type="email" name="email" value=""
                                        placeholder="user@example.test" required autocomplete="off" autofocus>
                                </div>
                                @error('email')
                                    <div class="auth-field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="auth-input-group">
                                <label for="password">Password</label>
                                <div class="auth-input-wrap auth-login-input @error('password') is-invalid @enderror">
                                    <input id="password" type="password" name="password" placeholder="Enter your password"
                                        required autocomplete="current-password">
                                    <button type="button" class="auth-password-toggle" data-password-toggle="password"
                                        aria-label="Toggle password visibility">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="auth-field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="auth-form-meta auth-login-meta">
                                <label class="auth-checkbox" for="remember">
                                    <input id="remember" type="checkbox" name="remember" value="1"
                                        {{ old('remember') ? 'checked' : '' }}>
                                    <span>Remember me</span>
                                </label>
                                <a href="{{ route('password.request') }}" class="auth-link" data-inline-spinner-link
                                    data-loading-text="Opening...">Forgot Password?</a>
                            </div>

                            <button type="submit" class="btn-brand auth-login-button auth-forgot-button" data-auth-submit
                                data-loading-text="Logging in...">
                                <span data-auth-submit-label>Login</span>
                            </button>
                        </form>

                        <div class="auth-login-back-wrap">
                            <p class="auth-login-switch-text">
                                Don't have an account?
                                <a href="{{ route('register') }}" class="auth-link">Create account</a>
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
