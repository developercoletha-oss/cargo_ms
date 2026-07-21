@extends('layouts.app')

@section('title', 'CFTMS - Modern Cargo and Freight Tracking Management System')

@push('critical-head')
    <style>
        .hero-section {
            padding: 120px 0;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 40%;
            height: 100%;
            background: radial-gradient(circle at center, rgba(59, 130, 246, 0.08) 0%, transparent 70%);
        }

        .home-hero-title {
            font-size: 3.5rem;
            line-height: 1.1;
            font-weight: 800;
        }

        .home-hero-copy {
            max-width: 650px;
            font-size: 1.25rem;
            line-height: 1.7;
            color: #475569;
        }

        .home-hero-actions {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-premium {
            padding: 18px 36px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            white-space: nowrap;
            font-size: 1.05rem;
        }

        .hero-logo-card {
            min-height: 380px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hero-logo-card img {
            width: 100%;
            max-width: 400px;
            height: auto;
            object-fit: contain;
            display: block;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: #64748b;
        }

        .significance-card {
            transition: all 0.3s ease;
            height: 100%;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
        }

        .significance-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }

        .significance-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }

        .significance-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }

        .significance-description {
            font-size: 0.95rem;
            line-height: 1.7;
            color: #64748b;
        }

        .tech-pill {
            display: inline-block;
            padding: 8px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-radius: 99px;
            font-size: 0.9rem;
            font-weight: 600;
            margin: 6px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        @media (max-width: 991.98px) {
            .hero-section {
                padding: 80px 0;
            }

            .home-hero-title {
                font-size: 2.5rem;
            }

            .home-hero-copy {
                max-width: 100%;
                font-size: 1.1rem;
            }

            .hero-logo-card {
                min-height: 280px;
                margin-top: 2rem;
                padding: 2rem !important;
            }

            .hero-logo-card img {
                max-width: 280px;
            }

            .section-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 575.98px) {
            .hero-section {
                padding: 60px 0 50px;
            }

            .home-hero-title {
                font-size: 2rem;
            }

            .home-hero-copy {
                font-size: 1rem;
                margin-bottom: 1.5rem !important;
            }

            .home-hero-actions {
                width: 100%;
                gap: 0.5rem;
                flex-direction: column;
            }

            .home-hero-actions .btn-premium {
                width: 100%;
                padding: 1rem;
                border-radius: 10px;
                font-size: 0.9rem;
            }

            .hero-logo-card {
                min-height: 220px;
                margin-top: 1.5rem;
                padding: 1.5rem !important;
            }

            .hero-logo-card img {
                max-width: 200px;
            }

            .tech-pill {
                font-size: 0.8rem;
                padding: 6px 14px;
            }
        }
    </style>
@endpush

@section('content')
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="home-hero-title fw-bold mb-4">
                        Take Control of Your Logistics with
                        <span class="text-primary">CFTMS</span>
                    </h1>
                    <p class="home-hero-copy text-muted mb-4">
                        The complete cargo and freight tracking platform that puts you in the driver's seat. Monitor every shipment,
                        optimize your fleet, and deliver exceptional service — all from one powerful dashboard.
                    </p>
                    <div class="home-hero-actions">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-premium shadow-lg">
                            Get Started
                        </a>
                        <a href="{{ route('about') }}" class="btn btn-outline-dark btn-premium">
                            <i class="bi bi-grid-3x3-gap me-2"></i> Explore the Platform
                        </a>
                    </div>

                    <div class="mt-4">
                        <div class="tech-pill"><i class="bi bi-broadcast me-1"></i> Local Route Tracking</div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="hero-logo-card p-5 bg-white rounded-4 shadow-sm border">
                        <img src="{{ asset('img/MYLOGO.png') }}"
                             alt="CFTMS Logo"
                             loading="eager">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" style="background: white;">
        <div class="container">
            <div class="row justify-content-center text-center mb-5">
                <div class="col-lg-8">
                    <h2 class="fw-bold mb-3" style="font-size: 2.5rem;">Built for Transport Companies That Demand Excellence</h2>
                    <p class="text-muted" style="font-size: 1.1rem;">Everything you need to track, manage, and scale your logistics operations with confidence</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm p-4 significance-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3" style="width: 56px; height: 56px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Accelerate Operations</h5>
                        </div>
                        <p class="text-muted mb-0">Automate workflows, eliminate bottlenecks, and get shipments moving faster with intelligent routing and dispatch.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm p-4 significance-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3" style="width: 56px; height: 56px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-file-earmark-check-fill"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Zero Paperwork Errors</h5>
                        </div>
                        <p class="text-muted mb-0">Go fully digital with automated documentation, electronic signatures, and error-proof data capture.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm p-4 significance-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3" style="width: 56px; height: 56px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-eye-fill"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Full Visibility & Control</h5>
                        </div>
                        <p class="text-muted mb-0">Monitor every shipment, vehicle, and driver in real-time with complete audit trails and status tracking.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm p-4 significance-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3" style="width: 56px; height: 56px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Delight Your Customers</h5>
                        </div>
                        <p class="text-muted mb-0">Provide instant tracking access, automated updates, and transparent delivery windows that build trust.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm p-4 significance-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3" style="width: 56px; height: 56px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Data-Driven Decisions</h5>
                        </div>
                        <p class="text-muted mb-0">Leverage analytics and insights to optimize routes, reduce costs, and make smarter logistics decisions.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
