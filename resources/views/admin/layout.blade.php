<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>POS System - Admin</title>
    
    {{-- CSS & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Alpine.js (The Verdict: Lightweight & Reactive) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    {{-- PWA Manifest --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1e1e2d">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root { 
            --sidebar-width: 280px; 
            --top-nav-height: 70px; 
            --primary-dark: #1e1e2d;
            --text-muted: #9899ac;
            --active-bg: rgba(54, 153, 255, 0.1);
            --active-text: #3699ff;
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
            left: 0; /* Default Open on Desktop */
            z-index: 1050;
            background-color: var(--primary-dark);
            color: var(--text-muted);
            transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 25px rgba(0,0,0,0.1);
        }

        /* --- STATE TOGGLING (Alpine-driven classes) --- */
        /* Desktop: When toggled, slide OUT to the left */
        @media (min-width: 768px) {
            body.sidebar-closed #sidebar-wrapper { transform: translateX(-100%); }
            body.sidebar-closed #page-content-wrapper { margin-left: 0; }
            #page-content-wrapper { margin-left: var(--sidebar-width); transition: margin-left 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); }
        }

        /* Mobile: Default hidden (slid out), When toggled (open), slide IN */
        @media (max-width: 767px) {
            #sidebar-wrapper { transform: translateX(-100%); } /* Hidden by default */
            body.sidebar-open #sidebar-wrapper { transform: translateX(0); } /* Slide In */
            
            #page-content-wrapper { margin-left: 0; width: 100%; }
            
            /* Backdrop for mobile */
            .sidebar-backdrop {
                display: none;
                position: fixed; inset: 0;
                background: rgba(0,0,0,0.5); z-index: 1040;
                backdrop-filter: blur(2px);
            }
            body.sidebar-open .sidebar-backdrop { display: block; }
        }

        /* --- HEADER --- */
        .sidebar-header {
            height: var(--top-nav-height);
            display: flex; align-items: center;
            padding: 0 20px;
            background-color: #1b1b28;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        /* --- MENU ITEMS (Fixed Spacing) --- */
        .sidebar-content { flex-grow: 1; overflow-y: auto; padding: 15px 0; }

        .list-group-item {
            background: transparent; border: none; color: var(--text-muted);
            padding: 12px 24px;
            font-size: 0.95rem; display: flex; align-items: center;
            border-left: 3px solid transparent;
            transition: all 0.2s;
            /* FIX: Ensure icon and text never bond */
            gap: 16px; 
        }
        
        .list-group-item i { 
            width: 20px; text-align: center; font-size: 1.1rem; 
            /* FIX: Prevent icon shrinking */
            flex-shrink: 0; 
        }
        
        .list-group-item:hover { background-color: rgba(255,255,255,0.03); color: #fff; }
        .list-group-item.active { background-color: var(--active-bg); color: var(--active-text); font-weight: 600; border-left-color: var(--active-text); }
        .menu-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #5d5f75; padding: 24px 24px 8px; font-weight: 700; }
        
        /* --- FOOTER (Fixed Visibility) --- */
        .sidebar-footer { 
            padding: 20px; 
            background-color: #1b1b28; 
            border-top: 1px solid rgba(255,255,255,0.05);
            flex-shrink: 0;
        }
        .user-info { display: flex; align-items: center; gap: 12px; margin-bottom: 15px; }
        .user-avatar { 
            width: 40px; height: 40px; 
            background: var(--active-text); color: white; 
            border-radius: 50%; display: flex; align-items: center; justify-content: center; 
            font-weight: bold; font-size: 1.1rem;
        }
        .user-details { overflow: hidden; }
        .user-name { color: white; font-weight: 600; font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { color: var(--text-muted); font-size: 0.8rem; text-transform: capitalize; }

        /* Logout Button Fix */
        .btn-logout {
            width: 100%;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            background: #2b2b40; color: #ff5b5b; border: 1px solid #ff5b5b;
            padding: 10px; border-radius: 8px; font-weight: 600; transition: all 0.2s;
        }
        .btn-logout:hover { background: #ff5b5b; color: white; }

        /* --- HAMBURGER BUTTONS --- */
        .hamburger-btn { background: transparent; border: none; padding: 5px; cursor: pointer; color: inherit; }
        
        /* Custom Scrollbar */
        .sidebar-content::-webkit-scrollbar { width: 5px; }
        .sidebar-content::-webkit-scrollbar-track { background: var(--primary-dark); }
        .sidebar-content::-webkit-scrollbar-thumb { background: #3b3b53; border-radius: 3px; }
    </style>
    @yield('styles')
</head>

{{-- ALPINE INITIALIZATION --}}
{{-- 
    sidebarOpen: Tracks state
    isDesktop: Helper to know screen size
    toggle(): Intelligent toggle based on screen size
--}}
<body 
    x-data="{ 
        sidebarOpen: false, 
        isDesktop: window.innerWidth >= 768,
        init() {
            // Check window resize to reset state if needed
            window.addEventListener('resize', () => {
                this.isDesktop = window.innerWidth >= 768;
            });
        },
        toggle() {
            this.sidebarOpen = !this.sidebarOpen;
        }
    }"
    :class="{ 
        'sidebar-closed': isDesktop && !sidebarOpen, 
        'sidebar-open': !isDesktop && sidebarOpen 
    }"
>

    {{-- Mobile Backdrop (Closes menu when clicked) --}}
    <div class="sidebar-backdrop" @click="sidebarOpen = false"></div>

    <div class="d-flex" id="wrapper">
        
        {{-- === SIDEBAR === --}}
        <div id="sidebar-wrapper">
            
            {{-- 1. HEADER --}}
            <div class="sidebar-header">
                {{-- Toggle Inside Sidebar (Closes it) --}}
                <button class="hamburger-btn me-3 text-muted" @click="toggle()">
                    <i class="fas fa-bars fa-lg"></i>
                </button>

                <div class="d-flex align-items-center">
                    <i class="fas fa-store text-primary me-2 fa-lg"></i> 
                    <span class="fw-bold text-white tracking-wide fs-5">SariPOS</span>
                </div>
            </div>

            {{-- 2. MENU CONTENT --}}
            <div class="sidebar-content">
                <div class="list-group list-group-flush">
                    
                    <a href="{{ route('cashier.pos') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-cash-register text-success"></i> 
                        <span>Go to Cashier</span>
                    </a>

                    @if(Auth::user()->role === 'admin')
                        <div class="menu-label">Overview</div>
                        <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                        </a>

                        <div class="menu-label">Inventory</div>
                        <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('products.*') ? 'active' : '' }}">
                            <i class="fas fa-box"></i> <span>Products List</span>
                        </a>
                        <a href="{{ route('inventory.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                            <i class="fas fa-warehouse"></i> <span>Stock Management</span>
                        </a>
                        <a href="{{ route('purchases.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                            <i class="fas fa-truck-loading"></i> <span>Restocking (In)</span>
                        </a>

                        <div class="menu-label">Finance & People</div>
                        <a href="{{ route('customers.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i> <span>Customers</span>
                        </a>
                        <a href="{{ route('credits.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('credits.*') ? 'active' : '' }}">
                            <i class="fas fa-wallet"></i> <span>Credits / Utang</span>
                        </a>
                        <a href="{{ route('suppliers.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                            <i class="fas fa-dolly"></i> <span>Suppliers</span>
                        </a>

                        <div class="menu-label">System</div>
                        <a href="{{ route('transactions.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('transaction_history.*') ? 'active' : '' }}">
                            <i class="fas fa-history"></i> <span>Transaction History</span>
                        </a>
                        <a href="{{ route('reports.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="fas fa-chart-pie"></i> <span>Reports & Analytics</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="fas fa-user-shield"></i> <span>User Management</span>
                        </a>
                        <a href="{{ route('logs.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('audit_logs.*') ? 'active' : '' }}">
                            <i class="fas fa-file-signature"></i> <span>Audit Logs</span>
                        </a>
                        <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="fas fa-cog"></i> <span>Settings</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- 3. FOOTER (Fixed) --}}
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="user-details">
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
            
            {{-- Top Navbar --}}
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm px-3 sticky-top" style="height: 70px;">
                
                {{-- Toggle Outside (Visible when needed via Alpine) --}}
                <button class="btn btn-light border me-3" @click="toggle()" x-show="!isDesktop || !sidebarOpen">
                    <i class="fas fa-bars"></i>
                </button>

                <h5 class="m-0 fw-bold text-dark d-none d-md-block">
                    @yield('title', 'Dashboard')
                </h5>

                <ul class="navbar-nav ms-auto align-items-center">
                    @if(Auth::user()->role === 'admin')
                    {{-- Alpine Dropdown for Notifications --}}
                    <li class="nav-item dropdown me-3" x-data="{ open: false }">
                        <a class="nav-link position-relative" href="#" @click.prevent="open = !open">
                            <i class="fas fa-bell fa-lg text-secondary"></i>
                            @if(($totalAlerts ?? 0) > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">
                                    {{ $totalAlerts ?? 0 }}
                                </span>
                            @endif
                        </a>
                        {{-- Dropdown Menu (Bootstrap class but Alpine controlled for safety) --}}
                        <div class="dropdown-menu dropdown-menu-end shadow border-0 p-0" :class="{ 'show': open }" @click.outside="open = false" style="width: 300px; right: 0; left: auto;">
                            <div class="p-3 border-bottom bg-light">
                                <h6 class="mb-0 fw-bold">Notifications</h6>
                            </div>
                            <div style="max-height: 300px; overflow-y: auto;">
                                @if(($outOfStockCount ?? 0) > 0)
                                    <a class="dropdown-item py-2 border-bottom" href="{{ route('products.index') }}">
                                        <small class="text-danger fw-bold"><i class="fas fa-exclamation-circle me-1"></i> Out of Stock</small>
                                        <div class="small text-muted">{{ $outOfStockCount }} items need restocking</div>
                                    </a>
                                @endif
                                @if(($lowStockCount ?? 0) > 0)
                                    <a class="dropdown-item py-2 border-bottom" href="{{ route('products.index') }}">
                                        <small class="text-warning fw-bold"><i class="fas fa-box-open me-1"></i> Low Stock</small>
                                        <div class="small text-muted">{{ $lowStockCount }} items are running low</div>
                                    </a>
                                @endif
                                @if(($totalAlerts ?? 0) == 0)
                                    <div class="p-3 text-center text-muted small">No new notifications</div>
                                @endif
                            </div>
                        </div>
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
</body>
</html>