<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System - Admin</title>
    
    {{-- CSS & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- PWA Manifest --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#212529">
    
    <style>
        :root { 
            --sidebar-width: 280px; 
            --top-nav-height: 70px; /* Increased slightly for better spacing */
        }
        
        body { 
            background-color: #f3f4f6; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden; 
        }

        /* --- SIDEBAR CONTAINER --- */
        #sidebar-wrapper {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: -280px;
            z-index: 1050;
            background-color: #1e1e2d;
            color: #9899ac;
            transition: left 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 25px rgba(0,0,0,0.15);
        }

        /* --- DESKTOP BEHAVIOR --- */
        @media (min-width: 768px) {
            #sidebar-wrapper { left: 0; }
            body.sb-sidenav-toggled #sidebar-wrapper { left: -280px; }
            
            #page-content-wrapper { margin-left: var(--sidebar-width); transition: margin 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); }
            body.sb-sidenav-toggled #page-content-wrapper { margin-left: 0; }

            /* CRITICAL FIX: Hide outside toggle when sidebar is OPEN on Desktop.
               Only show it when sidebar is CLOSED (toggled).
            */
            body:not(.sb-sidenav-toggled) #sidebarToggleTop {
                display: none !important;
            }
        }

        /* --- MOBILE BEHAVIOR --- */
        @media (max-width: 767px) {
            #page-content-wrapper { margin-left: 0; width: 100%; }
            body.sb-sidenav-toggled #sidebar-wrapper { left: 0; }
            
            .sidebar-backdrop {
                display: none;
                position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
                background: rgba(0,0,0,0.5); z-index: 1040;
                backdrop-filter: blur(2px);
                opacity: 0; transition: opacity 0.3s;
            }
            body.sb-sidenav-toggled .sidebar-backdrop { display: block; opacity: 1; }
        }

        /* --- HEADER ALIGNMENT (The "Line" Fix) --- */
        .sidebar-header {
            height: var(--top-nav-height);
            display: flex; 
            align-items: center;
            padding: 0 20px;
            background-color: #1b1b28;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            /* Ensure content inside aligns vertically */
        }

        .navbar { 
            height: var(--top-nav-height); 
            background: white; 
            display: flex;
            align-items: center;
            padding-top: 0;
            padding-bottom: 0;
        }

        /* --- ANIMATED HAMBURGER --- */
        .hamburger-btn {
            background: transparent; border: none; cursor: pointer;
            padding: 0; width: 30px; height: 30px;
            position: relative; display: flex; justify-content: center; align-items: center;
        }
        .hamburger-btn span {
            display: block; width: 22px; height: 2px;
            background-color: #9899ac; position: absolute;
            transition: all 0.3s ease-in-out; border-radius: 2px;
        }
        /* Inner Button (White) */
        .hamburger-btn.inner-toggle span { background-color: #9899ac; }
        .hamburger-btn.inner-toggle:hover span { background-color: white; }
        
        /* Outer Button (Dark) */
        .hamburger-btn.outer-toggle span { background-color: #333; }

        /* Icon Lines Positions */
        .hamburger-btn span:nth-child(1) { top: 8px; }
        .hamburger-btn span:nth-child(2) { top: 14px; }
        .hamburger-btn span:nth-child(3) { top: 20px; }

        /* Animation: Arrow Pointing Left */
        .hamburger-btn.is-active span:nth-child(1) { top: 14px; transform: rotate(45deg); }
        .hamburger-btn.is-active span:nth-child(2) { opacity: 0; transform: translateX(-10px); }
        .hamburger-btn.is-active span:nth-child(3) { top: 14px; transform: rotate(-45deg); }

        /* --- REST OF UI --- */
        .sidebar-content { flex-grow: 1; overflow-y: auto; padding: 10px 0; }
        .sidebar-footer { padding: 15px; background-color: #1b1b28; border-top: 1px solid rgba(255,255,255,0.05); }

        .list-group-item {
            background: transparent; border: none; color: #9899ac;
            padding: 12px 25px; font-size: 0.95rem; display: flex; align-items: center;
            border-left: 3px solid transparent;
        }
        .list-group-item i { width: 30px; text-align: center; font-size: 1.1rem; margin-right: 5px; }
        .list-group-item:hover { background-color: rgba(255,255,255,0.03); color: #fff; }
        .list-group-item.active { background-color: rgba(54, 153, 255, 0.1); color: #3699ff; font-weight: 600; border-left-color: #3699ff; }
        .menu-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #5d5f75; padding: 20px 25px 5px; font-weight: 600; }
        
        .sidebar-content::-webkit-scrollbar { width: 5px; }
        .sidebar-content::-webkit-scrollbar-track { background: #1e1e2d; }
        .sidebar-content::-webkit-scrollbar-thumb { background: #3b3b53; border-radius: 3px; }
    </style>
    @yield('styles')
</head>
<body>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="d-flex" id="wrapper">
        
        {{-- === SIDEBAR === --}}
        <div id="sidebar-wrapper">
            <div class="sidebar-header container-fluid px-4">
                {{-- INNER TOGGLE: Left Side --}}
                <button class="hamburger-btn inner-toggle is-active me-3 " id="sidebarToggleInside" title="Collapse Menu">
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

        {{-- === PAGE CONTENT === --}}
        <div id="page-content-wrapper">
            
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm px-3 sticky-top">
                
                {{-- OUTSIDE TOGGLE (Hidden on Desktop unless toggled) --}}
                <button class="hamburger-btn outer-toggle me-3" id="sidebarToggleTop" title="Open Menu">
                    <span></span><span></span><span></span>
                </button>

                <h5 class="m-0 fw-bold text-dark">
                    @yield('title', 'Dashboard')
                </h5>

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
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="width: 280px;">
                            <li><h6 class="dropdown-header text-uppercase fw-bold small">Alerts Center</h6></li>
                            @if(($outOfStockCount ?? 0) > 0)
                                <li><a class="dropdown-item rounded mb-1 text-danger bg-danger-subtle fw-medium" href="{{ route('products.index') }}"><i class="fas fa-exclamation-circle me-2"></i> {{ $outOfStockCount }} Items Out of Stock</a></li>
                            @endif
                            @if(($lowStockCount ?? 0) > 0)
                                <li><a class="dropdown-item rounded mb-1 text-warning bg-warning-subtle fw-medium" href="{{ route('products.index') }}"><i class="fas fa-box-open me-2"></i> {{ $lowStockCount }} Items Low Stock</a></li>
                            @endif
                            @if(($expiringCount ?? 0) > 0)
                                <li><a class="dropdown-item rounded mb-1 text-danger bg-danger-subtle fw-medium" href="{{ route('products.index') }}"><i class="fas fa-hourglass-end me-2"></i> {{ $expiringCount }} Items Expiring</a></li>
                            @endif
                            @if(($totalAlerts ?? 0) == 0)
                                <li class="text-center text-muted small py-3">No new notifications</li>
                            @endif
                        </ul>
                    </li>
                    @endif
                </ul>
            </nav>

            <div class="container-fluid p-4">
                @yield('content')
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtnInside = document.getElementById('sidebarToggleInside');
            const toggleBtnTop = document.getElementById('sidebarToggleTop');
            const backdrop = document.getElementById('sidebarBackdrop');
            const body = document.body;
            
            function toggleMenu() {
                body.classList.toggle('sb-sidenav-toggled');
                
                // Animation Logic
                const isClosed = body.classList.contains('sb-sidenav-toggled');
                const isDesktop = window.innerWidth >= 768;

                if (isDesktop) {
                    // Desktop: Toggled means CLOSED
                    if (isClosed) {
                        toggleBtnInside.classList.remove('is-active'); // Reset inner (though hidden)
                        toggleBtnTop.classList.remove('is-active');    // Show hamburger outside
                    } else {
                        toggleBtnInside.classList.add('is-active');    // Show Arrow inside
                        toggleBtnTop.classList.add('is-active');
                    }
                } else {
                    // Mobile: Toggled means OPEN
                    if (isClosed) {
                        toggleBtnInside.classList.add('is-active');    // Show Arrow inside
                    } else {
                        toggleBtnInside.classList.remove('is-active');
                    }
                }
            }

            if (toggleBtnInside) toggleBtnInside.addEventListener('click', toggleMenu);
            if (toggleBtnTop) toggleBtnTop.addEventListener('click', toggleMenu);
            if (backdrop) {
                backdrop.addEventListener('click', () => {
                    body.classList.remove('sb-sidenav-toggled');
                    toggleBtnInside.classList.remove('is-active');
                });
            }

            // Init State
            if (window.innerWidth >= 768 && !body.classList.contains('sb-sidenav-toggled')) {
                toggleBtnInside.classList.add('is-active');
            }
        });
    </script>
</body>
</html>