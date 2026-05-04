@extends('layouts.app')

@section('title', 'About CFTMS Cargo and Freight Tracking Management System')

@push('critical-head')
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
@endpush

@section('content')
    <section class="about-page">
        <div class="container">
            <div class="row g-4 align-items-stretch mb-4">
                <div class="col-lg-12">
                    <div class="about-hero-card h-100">
                        <span class="about-kicker"><i class="bi bi-stars"></i> About CFTMS</span>
                        <h1 class="about-title">A complete Cargo and Freight Tracking Management System for local transport companies.</h1>
                        <p class="about-copy">
                            CFTMS is designed to help transport companies manage their daily operations efficiently - tracking shipments,
                            managing fleets and drivers, handling client invoices, and monitoring delivery performance all in one integrated platform.
                        </p>
                        <ul class="about-list">
                            <li><i class="bi bi-check-circle-fill"></i><span>Real-time cargo tracking with GPS integration and status updates.</span></li>
                            <li><i class="bi bi-check-circle-fill"></i><span>Complete fleet and driver management to optimize transport resources.</span></li>
                            <li><i class="bi bi-check-circle-fill"></i><span>Client portal, invoicing system, and analytics dashboard for informed decision-making.</span></li>
                        </ul>
                    </div>
                </div>

            </div>

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <h2 class="about-section-title mb-0">Built for Transport Professionals</h2>
                <a href="{{ route('home') }}" class="btn btn-outline-primary px-4">
                    <i class="bi bi-arrow-left me-1"></i>Back to Home
                </a>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <article class="about-value-card">
                        <span class="about-value-icon"><i class="bi bi-diagram-3"></i></span>
                        <h3>Complete Operations Hub</h3>
                        <p>All tools needed to manage cargo shipments, fleets, drivers, clients, and finances in one centralized system.</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="about-value-card">
                        <span class="about-value-icon"><i class="bi bi-shield-lock"></i></span>
                        <h3>Secure Access Control</h3>
                        <p>Role-based authentication ensures only authorized personnel can access sensitive operational data and functions.</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="about-value-card">
                        <span class="about-value-icon"><i class="bi bi-lightning-charge"></i></span>
                        <h3>Real-time Updates</h3>
                        <p>Track shipments and fleet status in real-time, providing accurate ETAs and instant notifications to clients.</p>
                    </article>
                </div>
            </div>

            <div class="row g-3 mt-1 align-items-stretch">
                <div class="col-lg-7">
                    <article class="about-stack-card">
                        <h2 class="about-section-title">Languages & Technologies</h2>
                        <div class="about-stack-grid">
                            <div class="about-stack-item"><i class="bi bi-filetype-php"></i><span>PHP</span></div>
                            <div class="about-stack-item"><i class="bi bi-bootstrap-fill"></i><span>Bootstrap</span></div>
                            <div class="about-stack-item"><i class="bi bi-filetype-js"></i><span>JavaScript</span></div>
                            <div class="about-stack-item"><i class="bi bi-database-fill"></i><span>MySQL</span></div>
                            <div class="about-stack-item"><i class="bi bi-layers-fill"></i><span>Blade</span></div>
                        </div>
                    </article>
                </div>
                <div class="col-lg-5">
                    <article class="about-developer-card">
                        <h2 class="about-section-title">About the Developer</h2>
                        <p>
                            Built by Coletha Paulo, a developer focused on building practical transportation management solutions. CFTMS
                            was created to address the needs of local transport companies - providing a robust, scalable system for managing
                            cargo operations, improving delivery efficiency, and reducing administrative overhead.
                        </p>
                        <p>
                            Designed with real-world logistics workflows in mind, CFTMS combines clean architecture with features that
                            matter - shipment tracking, fleet management, and client relationship tools - all built on a foundation of
                            best practices for reliability and scalability.
                        </p>
                        <p>
                            Explore more projects and contributions:
                            <a href="#" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-github"></i> GitHub
                            </a>
                        </p>
                        <div class="about-developer-meta">
                            <span>Laravel</span>
                            <span>Logistics</span>
                            <span>Clean Code</span>
                            <span>Real-time Tracking</span>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
@endsection
