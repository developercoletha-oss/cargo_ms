@php
    $currentUser = Auth::user();
    $userRole = $currentUser?->role ?? 'user';
    $isAdministrationMenuOpen = request()->is('dashboard/profile*') || request()->is('dashboard/settings*');
    $isMonitoringMenuOpen = request()->is('dashboard/notifications*');
    $isShipmentsMenuOpen = request()->is('dashboard/shipments*');
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
                <div class="coletha-sidebar-brand-subtitle">Cargo and Freight Tracking</div>
            </div>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('dashboard.index') }}"
                    class="nav-link {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i> Dashboard
                </a>
            </li>

            @if(in_array($userRole, ['admin', 'hgadmin', 'manager', 'staff']))
            <li class="nav-item">
                <a href="{{ route('dashboard.shipments.index') }}"
                    class="nav-link {{ request()->routeIs('dashboard.shipments.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Shipments
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a href="{{ route('dashboard.notifications.index') }}"
                    class="nav-link {{ request()->is('dashboard/notifications*') ? 'active' : '' }}">
                    <i class="bi bi-bell-fill"></i> Notifications
                </a>
            </li>

            @if(in_array($userRole, ['admin', 'hgadmin']))
            <li class="nav-item">
                <a href="#" 
                    class="nav-link {{ $isAdministrationMenuOpen ? '' : '' }}" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#adminSubmenu"
                    aria-expanded="{{ $isAdministrationMenuOpen ? 'true' : 'false' }}">
                    <i class="bi bi-shield-lock"></i> Administration
                </a>
                <div class="collapse {{ $isAdministrationMenuOpen ? 'show' : '' }}" id="adminSubmenu">
                    <ul class="nav flex-column ms-3 mt-2">
                        <li class="nav-item">
                            <a href="{{ route('dashboard.profile.show') }}"
                                class="nav-link {{ request()->is('dashboard/profile*') ? 'active' : '' }}">
                                <i class="bi bi-person"></i> My Profile
                            </a>
                        </li>
                        @can('manage-users')
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="bi bi-people"></i> User Management
                            </a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </li>
            @else
            <li class="nav-item">
                <a href="{{ route('dashboard.profile.show') }}"
                    class="nav-link {{ request()->is('dashboard/profile*') ? 'active' : '' }}">
                    <i class="bi bi-person"></i> My Profile
                </a>
            </li>
            @endif

            <div class="coletha-sidebar-footer">
                <div class="coletha-sidebar-footer__copy">&copy; 2026 CFTMS Kit</div>
            </div>
        </ul>
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
