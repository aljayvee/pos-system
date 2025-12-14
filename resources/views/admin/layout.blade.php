<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>POS System - Admin</title>
    
    {{-- CSS & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1e1e2d">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root { 
            --sidebar-width: 280px; 
            --top-nav-height: 70px; 
            --primary-dark: #1e1e2d;
            --secondary-dark: #151521;
            --text-muted: #9899ac;
            --text-light: #e4e6ef;
            --active-bg: rgba(54, 153, 255, 0.1);
            --active-text: #3699ff;
            --danger-color: #f64e60;
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
            left: 0;
            z-index: 1050;
            background-color: var(--primary-dark);
            color: var(--text-muted);
            transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 25px rgba(0,0,0,0.1);
        }

        /* --- PAGE CONTENT CONTAINER --- */
        #page-content-wrapper {
            width: 100%;
            transition: margin-left 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        /* --- RESPONSIVE BEHAVIOR (FIXED: Targeted #app instead of body) --- */
        
        /* DESKTOP */
        @media (min-width: 768px) {
            /* Open State */
            #app.desktop-open #page-content-wrapper { margin-left: var(--sidebar-width); }
            
            /* Closed State */
            #app.desktop-closed #sidebar-wrapper { transform: translateX(-100%); }
            #app.desktop-closed #page-content-wrapper { margin-left: 0; }
        }

        /* MOBILE */
        @media (max-width: 767px) {
            #page-content-wrapper { margin-left: 0 !important; }
            
            /* Default Hidden */
            #sidebar-wrapper { transform: translateX(-100%); }
            
            /* Slide In */
            #app.mobile-open #sidebar-wrapper { transform: translateX(0); }
            
            /* Backdrop */
            .sidebar-backdrop {
                display: none;
                position: fixed; inset: 0;
                background: rgba(0,0,0,0.5); z-index: 1040;
                backdrop-filter: blur(2px);
            }
            #app.mobile-open .sidebar-backdrop { display: block; }
        }

        /* --- SIDEBAR HEADER --- */
        .sidebar-header {
            height: var(--top-nav-height);
            display: flex; align-items: center;
            padding: 0 24px;
            background-color: var(--secondary-dark);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        /* --- MENU ITEMS --- */
        .sidebar-content { flex-grow: 1; overflow-y: auto; padding: 20px 0; }

        .list-group-item {
            background: transparent; border: none; color: var(--text-muted);
            padding: 12px 24px;
            font-size: 0.95rem; font-weight: 500;
            display: flex; align-items: center;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
            text-decoration: none;
            gap: 16px; 
        }
        
        .list-group-item i { 
            width: 24px; text-align: center; font-size: 1.1rem; 
            flex-shrink: 0; transition: color 0.2s;
        }
        
        .list-group-item:hover { background-color: rgba(255,255,255,0.03); color: var(--text-light); }
        .list-group-item:hover i { color: white; }
        
        .list-group-item.active { 
            background-color: var(--active-bg); 
            color: var(--active-text); 
            font-weight: 600; 
            border-left-color: var(--active-text); 
        }
        .list-group-item.active i { color: var(--active-text); }

        .menu-label { 
            font-size: 0.7rem; text-transform: uppercase; 
            letter-spacing: 1.2px; color: #5d5f75; 
            padding: 24px 24px 8px; font-weight: 700; 
        }
        
        /* --- SIDEBAR FOOTER --- */
        .sidebar-footer { 
            padding: 20px 24px; 
            background-color: var(--secondary-dark); 
            border-top: 1px solid rgba(255,255,255,0.05);
            flex-shrink: 0;
        }

        .user-card {
            display: flex; align-items: center; gap: 12px; margin-bottom: 16px;
        }
        .user-avatar { 
            width: 42px; height: 42px; 
            background: var(--active-text); color: white; 
            border-radius: 8px; display: flex; align-items: center; justify-content: center; 
            font-weight: bold; font-size: 1.2rem;
            box-shadow: 0 4px 10px rgba(54, 153, 255, 0.3);
        }
        .user-info { overflow: hidden; }
        .user-name { 
            color: white; font-weight: 700; font-size: 0.95rem; 
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; 
            line-height: 1.2;
        }
        .user-role { 
            color: var(--text-muted); font-size: 0.75rem; 
            text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;
        }

        .btn-logout {
            width: 100%;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            background: rgba(246, 78, 96, 0.1); 
            color: var(--danger-color); 
            border: 1px solid transparent;
            padding: 10px; border-radius: 8px; 
            font-weight: 600; font-size: 0.9rem;
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-logout:hover { 
            background: var(--danger-color); 
            color: white; 
            box-shadow: 0 4px 12px rgba(246, 78, 96, 0.3);
        }

        /* --- NOTIFICATIONS --- */
        .notification-menu {
            width: 320px; border: 0; border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden; z-index: 1060;
        }
        @media (max-width: 767px) {
            .notification-menu {
                position: fixed !important; top: 75px !important;
                left: 50% !important; transform: translateX(-50%) !important;
                width: 92% !important; max-width: 360px;
            }
        }

        /* --- SCROLLBAR --- */
        .sidebar-content::-webkit-scrollbar { width: 5px; }
        .sidebar-content::-webkit-scrollbar-track { background: var(--primary-dark); }
        .sidebar-content::-webkit-scrollbar-thumb { background: #3b3b53; border-radius: 3px; }
    </style>
    @yield('styles')
</head>

<body>

    {{-- 
        VUE ROOT ELEMENT 
        We moved the :class binding here so Vue can actually control it.
    --}}
    <div id="app" :class="{ 'desktop-open': !isMobile && sidebarOpen, 'desktop-closed': !isMobile && !sidebarOpen, 'mobile-open': isMobile && sidebarOpen }">
        
        {{-- Mobile Backdrop --}}
        <div class="sidebar-backdrop" @click="sidebarOpen = false"></div>

        <div class="d-flex" id="wrapper">
            
            {{-- === SIDEBAR === --}}
            <div id="sidebar-wrapper">
                
                <div class="sidebar-header">
                    <div class="d-flex align-items-center flex-grow-1">
                        <i class="fas fa-store text-primary fa-lg me-3"></i> 
                        <span class="fw-bold text-white tracking-wide fs-5">SariPOS</span>
                    </div>
                    <button class="btn btn-link text-muted p-0 d-md-none" @click="sidebarOpen = false">
                        <i class="fas fa-times fa-lg"></i>
                    </button>
                </div>

                <div class="sidebar-content">
                    <div class="list-group list-group-flush">
                        
                        <a href="{{ route('cashier.pos') }}" class="list-group-item {{ request()->routeIs('cashier.pos') ? 'active' : '' }}">
                            <i class="fas fa-cash-register {{ request()->routeIs('cashier.pos') ? '' : 'text-success' }}"></i> 
                            <span>Cashier POS</span>
                        </a>

                        @if(Auth::user()->role === 'admin')
                            <div class="menu-label">Overview</div>
                            <a href="{{ route('admin.dashboard') }}" class="list-group-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                            </a>

                            <div class="menu-label">Inventory</div>
                            <a href="{{ route('products.index') }}" class="list-group-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                                <i class="fas fa-box"></i> <span>Products</span>
                            </a>
                            <a href="{{ route('inventory.index') }}" class="list-group-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                                <i class="fas fa-warehouse"></i> <span>Stock Level</span>
                            </a>
                            <a href="{{ route('purchases.index') }}" class="list-group-item {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                                <i class="fas fa-truck-loading"></i> <span>Restocking</span>
                            </a>

                            <div class="menu-label">Finance & People</div>
                            <a href="{{ route('customers.index') }}" class="list-group-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                                <i class="fas fa-users"></i> <span>Customers</span>
                            </a>
                            <a href="{{ route('credits.index') }}" class="list-group-item {{ request()->routeIs('credits.*') ? 'active' : '' }}">
                                <i class="fas fa-wallet"></i> <span>Credits (Utang)</span>
                            </a>
                            <a href="{{ route('suppliers.index') }}" class="list-group-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                                <i class="fas fa-dolly"></i> <span>Suppliers</span>
                            </a>

                            <div class="menu-label">System</div>
                            <a href="{{ route('transactions.index') }}" class="list-group-item {{ request()->routeIs('transaction_history.*') ? 'active' : '' }}">
                                <i class="fas fa-history"></i> <span>Transactions</span>
                            </a>
                            <a href="{{ route('reports.index') }}" class="list-group-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                <i class="fas fa-chart-pie"></i> <span>Reports</span>
                            </a>
                            <a href="{{ route('users.index') }}" class="list-group-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="fas fa-user-shield"></i> <span>Users</span>
                            </a>
                            <a href="{{ route('logs.index') }}" class="list-group-item {{ request()->routeIs('audit_logs.*') ? 'active' : '' }}">
                                <i class="fas fa-file-signature"></i> <span>Logs</span>
                            </a>
                            <a href="{{ route('settings.index') }}" class="list-group-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                                <i class="fas fa-cog"></i> <span>Settings</span>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="sidebar-footer">
                    <div class="user-card">
                        <div class="user-avatar">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="user-info">
                            <div class="user-name">{{ Auth::user()->name }}</div>
                            <div class="user-role">{{ ucfirst(Auth::user()->role) }}</div>
                        </div>
                    </div>
                    
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn-logout">
                            <i class="fas fa-sign-out-alt"></i> 
                            <span>LOGOUT</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- === PAGE CONTENT === --}}
            <div id="page-content-wrapper">
                
                <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm px-4 sticky-top" style="height: var(--top-nav-height);">
                    
                    <button class="btn btn-light border shadow-sm me-3" @click="sidebarOpen = !sidebarOpen">
                        <i class="fas fa-bars"></i>
                    </button>

                    <h5 class="m-0 fw-bold text-dark d-none d-md-block">
                        @yield('title', 'Dashboard')
                    </h5>

                    <ul class="navbar-nav ms-auto align-items-center">
                        @if(Auth::user()->role === 'admin')
                        
                        <li class="nav-item dropdown me-3 position-relative">
                            <a class="nav-link position-relative" href="#" @click.prevent="notifOpen = !notifOpen">
                                <i class="fas fa-bell fa-lg text-secondary"></i>
                                @if(($totalAlerts ?? 0) > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">
                                        {{ $totalAlerts ?? 0 }}
                                    </span>
                                @endif
                            </a>
                            
                            {{-- Notifications Dropdown --}}
                            <div class="dropdown-menu dropdown-menu-end notification-menu shadow p-0" 
                                 :class="{ 'show': notifOpen }" 
                                 @click.outside="notifOpen = false"
                                 style="position: absolute; right: 0; top: 100%;">
                                
                                <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold text-dark">Notifications</h6>
                                    @if(($totalAlerts ?? 0) > 0)
                                        <span class="badge bg-danger rounded-pill">{{ $totalAlerts }} New</span>
                                    @endif
                                </div>

                                <div style="max-height: 320px; overflow-y: auto;">
                                    @if(($outOfStockCount ?? 0) > 0)
                                        <a class="dropdown-item py-3 px-3 border-bottom d-flex align-items-start gap-3" href="{{ route('products.index') }}">
                                            <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px; height:32px">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-dark fw-bold small">Out of Stock</h6>
                                                <p class="mb-0 small text-danger">{{ $outOfStockCount }} products need restocking</p>
                                            </div>
                                        </a>
                                    @endif

                                    @if(($lowStockCount ?? 0) > 0)
                                        <a class="dropdown-item py-3 px-3 border-bottom d-flex align-items-start gap-3" href="{{ route('products.index') }}">
                                            <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px; height:32px">
                                                <i class="fas fa-box-open"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-dark fw-bold small">Running Low</h6>
                                                <p class="mb-0 small text-muted">{{ $lowStockCount }} items below reorder point</p>
                                            </div>
                                        </a>
                                    @endif

                                    @if(($expiringCount ?? 0) > 0)
                                        <a class="dropdown-item py-3 px-3 border-bottom d-flex align-items-start gap-3" href="{{ route('products.index') }}">
                                            <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px; height:32px">
                                                <i class="fas fa-hourglass-end"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-dark fw-bold small">Expiring Soon</h6>
                                                <p class="mb-0 small text-muted">{{ $expiringCount }} items expiring in 7 days</p>
                                            </div>
                                        </a>
                                    @endif

                                    @if(($totalAlerts ?? 0) == 0)
                                        <div class="p-4 text-center text-muted">
                                            <i class="fas fa-check-circle fa-2x mb-2 opacity-25"></i>
                                            <p class="mb-0 small">No new notifications</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                        @endif
                    </ul>
                </nav>

                {{-- Content Body --}}
                <div class="container-fluid p-4">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>