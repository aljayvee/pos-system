<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System - Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#212529">
    
    <style>
        :root { --sidebar-width: 280px; --top-nav-height: 70px; }
        body { background-color: #f3f4f6; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }

        /* --- SIDEBAR STYLES (Preserved) --- */
        #sidebar-wrapper {
            width: var(--sidebar-width); height: 100vh; position: fixed; top: 0; left: -280px;
            z-index: 1050; background-color: #1e1e2d; color: #9899ac;
            transition: left 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex; flex-direction: column;
        }
        
        @media (min-width: 768px) {
            #sidebar-wrapper { left: 0; }
            body.sb-sidenav-toggled #sidebar-wrapper { left: -280px; }
            #page-content-wrapper { margin-left: var(--sidebar-width); transition: margin 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); }
            body.sb-sidenav-toggled #page-content-wrapper { margin-left: 0; }
            body:not(.sb-sidenav-toggled) #sidebarToggleTop { display: none !important; }
        }

        @media (max-width: 767px) {
            #page-content-wrapper { margin-left: 0; width: 100%; }
            body.sb-sidenav-toggled #sidebar-wrapper { left: 0; }
            .sidebar-backdrop {
                display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
                background: rgba(0,0,0,0.5); z-index: 1040; backdrop-filter: blur(2px);
            }
            body.sb-sidenav-toggled .sidebar-backdrop { display: block; }
        }

        .sidebar-header { height: var(--top-nav-height); display: flex; align-items: center; padding: 0 20px; background: #1b1b28; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-content { flex-grow: 1; overflow-y: auto; padding: 10px 0; }
        .sidebar-footer { padding: 15px; background: #1b1b28; border-top: 1px solid rgba(255,255,255,0.05); }
        .navbar { height: var(--top-nav-height); background: white; display: flex; align-items: center; }

        /* Hamburger Animation */
        .hamburger-btn { background: transparent; border: none; padding: 0; width: 30px; height: 30px; position: relative; display: flex; justify-content: center; align-items: center; }
        .hamburger-btn span { display: block; width: 22px; height: 2px; background-color: #9899ac; position: absolute; transition: all 0.3s; border-radius: 2px; }
        .hamburger-btn.inner-toggle:hover span { background-color: white; }
        .hamburger-btn.outer-toggle span { background-color: #333; }
        .hamburger-btn span:nth-child(1) { top: 8px; }
        .hamburger-btn span:nth-child(2) { top: 14px; }
        .hamburger-btn span:nth-child(3) { top: 20px; }
        .hamburger-btn.is-active span:nth-child(1) { top: 14px; transform: rotate(45deg); }
        .hamburger-btn.is-active span:nth-child(2) { opacity: 0; transform: translateX(-10px); }
        .hamburger-btn.is-active span:nth-child(3) { top: 14px; transform: rotate(-45deg); }

        .list-group-item { background: transparent; border: none; color: #9899ac; padding: 12px 25px; display: flex; align-items: center; border-left: 3px solid transparent; }
        .list-group-item:hover { background: rgba(255,255,255,0.03); color: white; }
        .list-group-item.active { background: rgba(54,153,255,0.1); color: #3699ff; font-weight: 600; border-left-color: #3699ff; }
        .menu-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #5d5f75; padding: 20px 25px 5px; font-weight: 600; }
        .sidebar-content::-webkit-scrollbar { width: 5px; background: #1e1e2d; }
        .sidebar-content::-webkit-scrollbar-thumb { background: #3b3b53; border-radius: 3px; }

        /* --- NOTIFICATION DROPDOWN FIX (Mobile & Desktop) --- */
        .notification-menu {
            width: 320px;
            padding: 0;
            border: 0;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .notification-header {
            background: #f8f9fa;
            padding: 12px 16px;
            border-bottom: 1px solid #e9ecef;
            display: flex; justify-content: space-between; align-items: center;
        }

        .notification-item {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f1f1;
            display: flex;
            align-items: flex-start;
            transition: background 0.2s;
            text-decoration: none;
            color: inherit;
        }
        .notification-item:hover { background-color: #f8fbff; }
        .notification-item:last-child { border-bottom: none; }

        .notif-icon {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            margin-right: 12px;
        }

        /* MOBILE SPECIFIC OVERRIDES */
        @media (max-width: 767px) {
            .notification-menu {
                position: fixed !important;
                top: 75px !important; /* Just below navbar */
                left: 50% !important;
                transform: translateX(-50%) !important;
                width: 90% !important; /* 90% Screen Width */
                max-width: 360px;
                box-shadow: 0 0 0 100vh rgba(0,0,0,0.3) !important; /* Dim Background */
            }
            
            /* Add arrow to point to top */
            .notification-menu::before {
                content: ''; position: absolute; top: -8px; right: 20px;
                border-left: 8px solid transparent; border-right: 8px solid transparent;
                border-bottom: 8px solid #f8f9fa;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="d-flex" id="wrapper">
        
        {{-- SIDEBAR --}}
        <div id="sidebar-wrapper">
            <div class="sidebar-header">
                <button class="hamburger-btn inner-toggle is-active me-3" id="sidebarToggleInside" title="Collapse Menu">
                    <span></span><span></span><span></span>
                </button>
                <div class="d-flex align-items-center">
                    <i class="fas fa-store text-primary me-2 fa-lg"></i> 
                    <span class="fw-bold text-white tracking-wide">SariPOS</span>
                </div>
            </div>

            <div class="sidebar-content">
                <div class="list-group list-group-flush">
                    <a href="{{ route('cashier.pos') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-cash-register text-success"></i> <span>Open Cashier POS</span>
                    </a>

                    @if(Auth::user()->role === 'admin')
                        <div class="menu-label">Overview</div>
                        <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                        </a>

                        <div class="menu-label">Inventory</div>
                        <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('products.*') ? 'active' : '' }}">
                            <i class="fas fa-box"></i> <span>Products</span>
                        </a>
                        <a href="{{ route('inventory.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                            <i class="fas fa-warehouse"></i> <span>Stock</span>
                        </a>
                        <a href="{{ route('purchases.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                            <i class="fas fa-truck-loading"></i> <span>Restocking</span>
                        </a>

                        <div class="menu-label">Finance</div>
                        <a href="{{ route('customers.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i> <span>Customers</span>
                        </a>
                        <a href="{{ route('credits.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('credits.*') ? 'active' : '' }}">
                            <i class="fas fa-wallet"></i> <span>Credits</span>
                        </a>
                        <a href="{{ route('suppliers.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                            <i class="fas fa-dolly"></i> <span>Suppliers</span>
                        </a>

                        <div class="menu-label">System</div>
                        <a href="{{ route('transactions.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('transaction_history.*') ? 'active' : '' }}">
                            <i class="fas fa-history"></i> <span>Transactions</span>
                        </a>
                        <a href="{{ route('reports.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="fas fa-chart-pie"></i> <span>Reports</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="fas fa-user-shield"></i> <span>Users</span>
                        </a>
                        <a href="{{ route('logs.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('audit_logs.*') ? 'active' : '' }}">
                            <i class="fas fa-file-signature"></i> <span>Logs</span>
                        </a>
                        <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="fas fa-cog"></i> <span>Settings</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="d-flex align-items-center mb-3 px-2">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="fw-bold text-white text-truncate" style="font-size: 0.9rem;">{{ Auth::user()->name }}</div>
                        <div class="text-muted small text-truncate">{{ ucfirst(Auth::user()->role) }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-danger w-100 btn-sm fw-bold">
                        <i class="fas fa-sign-out-alt me-1"></i> LOGOUT
                    </button>
                </form>
            </div>
        </div>

        {{-- PAGE CONTENT --}}
        <div id="page-content-wrapper">
            
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm px-3 sticky-top">
                <button class="hamburger-btn outer-toggle me-3" id="sidebarToggleTop" title="Open Menu">
                    <span></span><span></span><span></span>
                </button>

                <h5 class="m-0 fw-bold text-dark">@yield('title', 'Dashboard')</h5>

                <ul class="navbar-nav ms-auto align-items-center">
                    @if(Auth::user()->role === 'admin')
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link position-relative" href="#" id="alertsDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-bell fa-lg text-secondary"></i>
                            @if($totalAlerts ?? 0 > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">
                                    {{ $totalAlerts }}
                                </span>
                            @endif
                        </a>
                        
                        {{-- === IMPROVED NOTIFICATION DROPDOWN === --}}
                        <div class="dropdown-menu dropdown-menu-end notification-menu shadow" aria-labelledby="alertsDropdown">
                            <div class="notification-header">
                                <h6 class="mb-0 fw-bold text-uppercase small text-muted">Notifications</h6>
                                @if($totalAlerts ?? 0 > 0)
                                    <span class="badge bg-danger rounded-pill">{{ $totalAlerts }} New</span>
                                @endif
                            </div>

                            {{-- Item: Out of Stock --}}
                            @if(($outOfStockCount ?? 0) > 0)
                                <a class="notification-item" href="{{ route('products.index') }}">
                                    <div class="notif-icon bg-danger-subtle text-danger">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-dark fw-bold small">Out of Stock</h6>
                                        <p class="mb-0 small text-danger">{{ $outOfStockCount }} products need restocking</p>
                                    </div>
                                </a>
                            @endif

                            {{-- Item: Low Stock --}}
                            @if(($lowStockCount ?? 0) > 0)
                                <a class="notification-item" href="{{ route('products.index') }}">
                                    <div class="notif-icon bg-warning-subtle text-warning">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-dark fw-bold small">Running Low</h6>
                                        <p class="mb-0 small text-muted">{{ $lowStockCount }} items below reorder point</p>
                                    </div>
                                </a>
                            @endif

                            {{-- Item: Expiring --}}
                            @if(($expiringCount ?? 0) > 0)
                                <a class="notification-item" href="{{ route('products.index') }}">
                                    <div class="notif-icon bg-danger-subtle text-danger">
                                        <i class="fas fa-hourglass-end"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-dark fw-bold small">Expiring Soon</h6>
                                        <p class="mb-0 small text-muted">{{ $expiringCount }} items expiring in 7 days</p>
                                    </div>
                                </a>
                            @endif

                            {{-- No Notifications --}}
                            @if(($totalAlerts ?? 0) == 0)
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle fa-2x mb-2 opacity-25"></i>
                                    <p class="mb-0 small">All caught up!</p>
                                </div>
                            @endif
                        </div>
                        {{-- === END NOTIFICATION DROPDOWN === --}}
                    </li>
                    @endif
                </ul>
            </nav>

            <div class="container-fluid p-4">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtnInside = document.getElementById('sidebarToggleInside');
            const toggleBtnTop = document.getElementById('sidebarToggleTop');
            const backdrop = document.getElementById('sidebarBackdrop');
            const body = document.body;
            
            function toggleMenu() {
                body.classList.toggle('sb-sidenav-toggled');
                const isClosed = body.classList.contains('sb-sidenav-toggled');
                const isDesktop = window.innerWidth >= 768;

                if (isDesktop) {
                    if (isClosed) {
                        toggleBtnInside.classList.remove('is-active'); toggleBtnTop.classList.remove('is-active');
                    } else {
                        toggleBtnInside.classList.add('is-active'); toggleBtnTop.classList.add('is-active');
                    }
                } else {
                    if (isClosed) {
                        toggleBtnInside.classList.add('is-active');
                    } else {
                        toggleBtnInside.classList.remove('is-active');
                    }
                }
            }

            if (toggleBtnInside) toggleBtnInside.addEventListener('click', toggleMenu);
            if (toggleBtnTop) toggleBtnTop.addEventListener('click', toggleMenu);
            if (backdrop) backdrop.addEventListener('click', () => { 
                body.classList.remove('sb-sidenav-toggled');
                toggleBtnInside.classList.remove('is-active');
            });

            if (window.innerWidth >= 768 && !body.classList.contains('sb-sidenav-toggled')) {
                toggleBtnInside.classList.add('is-active');
            }
        });
    </script>
</body>
</html>