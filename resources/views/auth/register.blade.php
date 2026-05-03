@extends('layouts.auth')

@section('title', 'Register - CFTMS')

@push('critical-head')
    <style>
        .auth-register-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 2rem 0;
        }

        .auth-register-frame {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .auth-register-shell {
            display: flex;
            min-height: 650px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .auth-register-brand-panel {
            flex: 1.2;
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .auth-register-brand-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .auth-register-brand-panel::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 200px;
            background: linear-gradient(to top, rgba(0,0,0,0.2), transparent);
            pointer-events: none;
        }

        .auth-register-brand {
            display: flex;
            align-items: center;
            margin-bottom: 3rem;
            position: relative;
            z-index: 2;
            text-decoration: none;
            color: white;
            gap: 14px;
        }

        .auth-register-brand-img {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            object-fit: contain;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .auth-register-brand span {
            display: flex;
            flex-direction: column;
        }

        .auth-register-brand strong {
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .auth-register-brand small {
            font-size: 0.85rem;
            opacity: 0.8;
            font-weight: 500;
        }

        .auth-register-copy {
            position: relative;
            z-index: 2;
            margin-bottom: 2.5rem;
        }

        .auth-register-copy h1 {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 1rem;
        }

        .auth-register-description {
            font-size: 1.05rem;
            line-height: 1.7;
            opacity: 0.95;
            max-width: 400px;
        }

        .auth-register-watermark {
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

        .auth-register-instructions {
            margin-top: 1.5rem;
        }

        .auth-register-instructions p {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
        }

        .auth-register-instructions p:last-child {
            margin-bottom: 0;
        }

        .auth-register-instructions i {
            font-size: 1.1rem;
            opacity: 0.85;
        }

        .auth-register-form-panel {
            flex: 1;
            padding: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
        }

        .auth-register-form-card {
            width: 100%;
            max-width: 420px;
        }

        .auth-register-title-row {
            margin-bottom: 0.5rem;
        }

        .auth-register-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .auth-register-title i {
            color: #3b82f6;
        }

        .auth-register-subtitle {
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

        .auth-register-button {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);
        }

        .auth-register-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px -5px rgba(59, 130, 246, 0.5);
        }

        .auth-register-back-wrap {
            margin-top: 1.5rem;
            text-align: center;
        }

        .auth-register-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .auth-register-back-link:hover {
            color: #374151;
        }

        .input-group-text {
            background: #f8fafc;
            border-color: #e5e7eb;
            color: #6b7280;
        }

        @media (max-width: 991.98px) {
            .auth-register-shell {
                flex-direction: column;
                max-width: 600px;
                margin: 0 auto;
            }

            .auth-register-brand-panel {
                padding: 2rem;
                min-height: 300px;
            }

            .auth-register-copy h1 {
                font-size: 1.75rem;
            }

            .auth-register-form-panel {
                padding: 2rem;
            }
        }

        @media (max-width: 575.98px) {
            .auth-register-page {
                padding: 1rem 0;
            }

            .auth-register-frame {
                padding: 0 1rem;
            }

            .auth-register-shell {
                border-radius: 16px;
            }

            .auth-register-brand-panel {
                padding: 1.5rem;
                min-height: 250px;
            }

            .auth-register-copy h1 {
                font-size: 1.5rem;
            }

            .auth-register-form-panel {
                padding: 1.5rem;
            }

            .auth-register-title {
                font-size: 1.5rem;
            }
        }

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

        .auth-register-form-card {
            animation: fade-in-up 0.6s ease-out;
        }
    </style>
@endpush

@section('content')
    <section class="auth-page auth-register-page">
        <div class="auth-register-frame">
            <div class="auth-register-shell">
                <aside class="auth-register-brand-panel">
                    <div class="auth-register-brand">
                        <img src="{{ asset('img/MYLOGO.png') }}" alt="CFTMS Logo" class="auth-register-brand-img">
                        <span>
                            <strong>CFTMS</strong>
                            <small>Cargo and Freight Tracking Management System</small>
                        </span>
                    </div>

                    <div class="auth-register-copy">
                        <h1>Join the cargo network today.</h1>
                        <p class="auth-register-description">
                            Create an account to start managing shipments and tracking your freight operations.
                        </p>
                        <div class="auth-register-instructions">
                            <p><i class="bi bi-person"></i> Enter your full name.</p>
                            <p><i class="bi bi-envelope"></i> Provide your email address.</p>
                            <p><i class="bi bi-key"></i> Set a secure password.</p>
                        </div>
                    </div>
                </aside>

                <div class="auth-register-form-panel">
                    <div class="auth-register-form-card">
                        <div class="auth-register-title-row">
                            <h2 class="auth-register-title">
                                <i class="bi bi-person-plus"></i>
                                <span>Create Account</span>
                            </h2>
                        </div>

                        <p class="auth-register-subtitle">Sign up for a new CFTMS account.</p>

                         @include('auth.partials.feedback')

                        <a href="{{ route('login') }}" class="auth-register-link" style="position: absolute; top: 1.5rem; right: 1.5rem; font-size: 0.9rem; color: #6b7280; text-decoration: none;">
                            <i class="bi bi-arrow-left"></i> <span style="text-decoration: underline;">Back to Login</span>
                        </a>

                        <form method="POST" action="{{ route('register') }}" class="auth-form auth-register-form" data-auth-form>
                            @csrf

                            <div class="auth-input-group">
                                <label for="full_name">Full Name</label>
                                <div class="auth-input-wrap auth-register-input @error('full_name') is-invalid @enderror">
                                    <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" placeholder="John Doe" required autocomplete="name">
                                </div>
                                @error('full_name')
                                    <div class="auth-field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="auth-input-group">
                                <label for="name">Username</label>
                                <div class="auth-input-wrap auth-register-input @error('name') is-invalid @enderror">
                                    <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="johndoe" required autocomplete="username">
                                </div>
                                @error('name')
                                    <div class="auth-field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="auth-input-group">
                                <label for="email">Email address</label>
                                <div class="auth-input-wrap auth-register-input @error('email') is-invalid @enderror">
                                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="user@example.test" required autocomplete="email">
                                </div>
                                @error('email')
                                    <div class="auth-field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="auth-input-group">
                                <label for="password">Password</label>
                                <div class="auth-input-wrap auth-register-input @error('password') is-invalid @enderror">
                                    <input id="password" type="password" name="password" placeholder="Minimum 8 characters" required autocomplete="new-password">
                                    <button type="button" class="auth-password-toggle" data-password-toggle="password" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="auth-field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="auth-input-group">
                                <label for="password_confirmation">Confirm Password</label>
                                <div class="auth-input-wrap auth-register-input">
                                    <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Re-type your password" required autocomplete="new-password">
                                    <button type="button" class="auth-password-toggle" data-password-toggle="password_confirmation" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn-brand auth-register-button" data-auth-submit data-loading-text="Creating Account...">
                                <span data-auth-submit-label>Create Account</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection