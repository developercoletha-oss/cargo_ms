@extends('layouts.auth')

@section('title', 'Login - CFTMS')

@push('critical-head')
    <style>
        .auth-login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 2rem 0;
        }

        .auth-login-frame {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .auth-login-shell {
            display: flex;
            min-height: 600px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .auth-login-brand-panel {
            flex: 1.2;
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .auth-login-brand-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .auth-login-brand-panel::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 200px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.2), transparent);
            pointer-events: none;
        }

        .auth-login-brand {
            display: flex;
            align-items: center;
            margin-bottom: 3rem;
            position: relative;
            z-index: 2;
            text-decoration: none;
            color: white;
            gap: 14px;
        }

        .auth-login-brand-img {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            object-fit: contain;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .auth-login-brand span {
            display: flex;
            flex-direction: column;
        }

        .auth-login-brand strong {
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .auth-login-brand small {
            font-size: 0.85rem;
            opacity: 0.8;
            font-weight: 500;
        }

        .auth-login-copy {
            position: relative;
            z-index: 2;
            margin-bottom: 2.5rem;
        }

        .auth-login-copy h1 {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 1rem;
        }

        .auth-login-description {
            font-size: 1.05rem;
            line-height: 1.7;
            opacity: 0.95;
            max-width: 400px;
        }

        .auth-login-watermark {
            position: absolute;
            right: -30px;
            bottom: -30px;
            width: 200px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10rem;
            opacity: 0.08;
            pointer-events: none;
        }

        .auth-login-instructions {
            margin-top: 1.5rem;
        }

        .auth-login-instructions p {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
        }

        .auth-login-instructions p:last-child {
            margin-bottom: 0;
        }

        .auth-login-instructions i {
            font-size: 1.1rem;
            opacity: 0.85;
        }

        .auth-login-form-panel {
            flex: 1;
            padding: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
        }

        .auth-login-form-card {
            width: 100%;
            max-width: 420px;
            min-height: 100%;
            display: flex;
            flex-direction: column;
        }

        .auth-login-title-row {
            margin-bottom: 0.5rem;
        }

        .auth-login-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .auth-login-title i {
            color: #3b82f6;
        }

        .auth-login-subtitle {
            color: #64748b;
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .auth-input-group {
            margin-bottom: 1.5rem;
        }

        .auth-input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
        }

        .auth-input-wrap {
            position: relative;
        }

        .auth-input-wrap input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s ease;
            outline: none;
        }

        .auth-input-wrap input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .auth-password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 4px;
        }

        .auth-password-toggle:hover {
            color: #374151;
        }

        .auth-field-error {
            color: #dc2626;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        .auth-form-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .auth-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: #374151;
        }

        .auth-checkbox input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .auth-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-link:hover {
            text-decoration: underline;
        }

        .btn-brand {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .auth-login-button {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);
        }

        .auth-login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px -5px rgba(59, 130, 246, 0.5);
        }

        .auth-login-back-wrap {
            margin-top: auto;
            padding-top: 1.5rem;
            text-align: center;
        }

        .auth-login-home-wrap {
            margin-top: 0.75rem;
            display: flex;
            width: 100%;
            justify-content: center !important;
            text-align: center;
        }

        .auth-login-home-link {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: #1e293b;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            padding: 0.5rem 0.9rem;
            text-decoration: none !important;
            border-bottom: 0 !important;
            box-shadow: none !important;
            font-size: 0.92rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .auth-login-home-link:hover,
        .auth-login-home-link:focus,
        .auth-login-home-link:active {
            color: #0f172a;
            background: #e2e8f0;
            border-color: #94a3b8;
            transform: translateY(-1px);
            text-decoration: none !important;
            border-bottom: 0 !important;
            box-shadow: none !important;
            outline: none;
        }

        .auth-login-back-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            color: #6b7280;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s ease;
            justify-content: center;
        }

        .auth-login-back-link:hover {
            color: #374151;
        }

        .input-group-text {
            background: #f8fafc;
            border-color: #e5e7eb;
            color: #6b7280;
        }

        @media (max-width: 991.98px) {
            .auth-login-shell {
                flex-direction: column;
                max-width: 600px;
                margin: 0 auto;
            }

            .auth-login-brand-panel {
                padding: 2rem;
                min-height: 300px;
            }

            .auth-login-copy h1 {
                font-size: 1.75rem;
            }

            .auth-login-form-panel {
                padding: 2rem;
            }
        }

        @media (max-width: 575.98px) {
            .auth-login-page {
                padding: 1rem 0;
            }

            .auth-login-frame {
                padding: 0 1rem;
            }

            .auth-login-shell {
                border-radius: 16px;
            }

            .auth-login-brand-panel {
                padding: 1.5rem;
                min-height: 250px;
            }

            .auth-login-copy h1 {
                font-size: 1.5rem;
            }

            .auth-login-guide li {
                font-size: 0.95rem;
            }

            .auth-login-form-panel {
                padding: 1.5rem;
            }

            .auth-login-title {
                font-size: 1.5rem;
            }
        }

        /* Smooth animations */
        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-login-form-card {
            animation: fade-in-up 0.6s ease-out;
        }
    </style>
@endpush

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
                        <div class="auth-login-instructions">
                            <p><i class="bi bi-envelope"></i> Enter your email address.</p>
                            <p><i class="bi bi-key"></i> Type your password.</p>
                            <p><i class="bi bi-box-arrow-in-right"></i> Click Login to continue.</p>
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

                        <a href="{{ route('register') }}" class="auth-register-link"
                            style="position: absolute; top: 1.5rem; right: 1.5rem; font-size: 0.9rem; color: #6b7280; text-decoration: none;">
                            <i class="bi bi-person-plus"></i> <span style="text-decoration: underline;">Create
                                Account</span>
                        </a>

                        <form method="POST" action="{{ route('login.submit') }}" class="auth-form auth-login-form"
                            data-auth-form>
                            @csrf

                            <div class="auth-input-group">
                                <label for="email">Email address</label>
                                <div class="auth-input-wrap auth-login-input @error('email') is-invalid @enderror">
                                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                                        placeholder="user@example.test" required autocomplete="email" autofocus>
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
                            <p style="margin-bottom: 0.75rem; color: #64748b; font-size: 0.92rem;">
                                Don't have an account?
                                <a href="{{ route('register') }}" class="auth-link">Create account</a>
                            </p>
                        </div>

                        <div class="auth-login-home-wrap"
                            style="width: 100%; display: flex; justify-content: center; text-align: center;">
                            <a href="{{ route('home') }}" class="auth-login-home-link"
                                style="display:inline-flex;align-items:center;justify-content:center;gap:.45rem;color:#1e293b;background:transparent;border:none;border-radius:0;padding:0;text-decoration:none !important;border-bottom:0 !important;box-shadow:none !important;font-size:.92rem;font-weight:600;line-height:1;">
                                <i class="bi bi-arrow-left" style="text-decoration:none !important;"></i>
                                <span style="text-decoration:none !important;">Back to Home</span>
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
