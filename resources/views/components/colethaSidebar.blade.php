@php
    $isAdministrationMenuOpen = request()->is('dashboard/profile*') || request()->is('dashboard/settings*');
    $isMonitoringMenuOpen = request()->is('dashboard/notifications*');
@endphp

<aside id="sidebar" class="CFTMS-sidebar">
    <div class="p-3 coletha-sidebar-inner">
        <div class="coletha-sidebar-brand mb-4">
            <div class="coletha-sidebar-logo" aria-hidden="true">
                <img src="{{ asset('img/MYLOGO.png') }}" alt="Logo"
                    style="width: 32px; height: 32px; border-radius: 6px;">
            </div>
            <div class="coletha-sidebar-brand-text">
                <div class="coletha-sidebar-brand-title">CFTMS</div>
                <div class="coletha-sidebar-brand-subtitle">Cargo and Freight Tracking Management System</div>
            </div>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('dashboard.index') }}"
                    class="nav-link {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i> Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('dashboard.notifications.index') }}"
                    class="nav-link {{ request()->is('dashboard/notifications*') ? 'active' : '' }}">
                    <i class="bi bi-bell-fill"></i> Notifications
                </a>
            </li>


            <div class="coletha-sidebar-footer">
                <div class="coletha-sidebar-footer__copy">&copy; 2025 CFTMS Kit</div>
            </div>
    </div>
</aside>

<style>
    .CFTMS-sidebar .nav-link {
        border-radius: 12px;
        margin-bottom: 4px;
        padding: 10px 16px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .CFTMS-sidebar .nav-link:hover {
        background: rgba(var(--color-primary-500-rgb), 0.08);
        color: var(--color-primary-500);
    }

    .CFTMS-sidebar .nav-link.active {
        background: var(--color-primary-500);
        color: var(--color-white);
        box-shadow: 0 8px 16px rgba(var(--color-primary-500-rgb), 0.2);
    }
</style>
