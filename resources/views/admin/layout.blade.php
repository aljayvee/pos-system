<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- PWA Manifest & Icons --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#212529">
    <link rel="apple-touch-icon" href="https://cdn-icons-png.flaticon.com/512/3081/3081559.png">
    
    <style>
        body { overflow-x: hidden; background-color: #f8f9fa; }
        
        #sidebar-wrapper {
            min-height: 100vh;
            margin-left: -15rem;
            transition: margin 0.25s ease-out;
            background-color: #212529; 
            color: white;
        }
        
        #sidebar-wrapper .sidebar-heading {
            padding: 0.875rem 1.25rem;
            font-size: 1.2rem;
            font-weight: bold;
            background-color: #1a1e21; 
            border-bottom: 1px solid #495057;
        }
        
        #sidebar-wrapper .list-group { width: 15rem; }
        #page-content-wrapper { min-width: 100vw; transition: margin 0.25s ease-out; }
        body.sb-sidenav-toggled #sidebar-wrapper { margin-left: 0; }
        
        .list-group-item {
            border: none;
            padding: 15px 20px;
            background-color: #212529;
            color: #d1d5db;
        }
        .list-group-item:hover { background-color: #343a40; color: #fff; }
        .list-group-item.active { background-color: #0d6efd; color: white; font-weight: bold; }
        .list-group-item i { width: 25px; }

        /* Submenu Toggle Style */
        .submenu-toggle {
            cursor: pointer;
            font-weight: bold;
            color: #adb5bd !important; /* Lighter text for visibility */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .submenu-toggle:hover { color: #fff !important; }

        @media (min-width: 768px) {
            #sidebar-wrapper { margin-left: 0; }
            #page-content-wrapper { min-width: 0; width: 100%; }
            body.sb-sidenav-toggled #sidebar-wrapper { margin-left: -15rem; }
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

    <div class="d-flex" id="wrapper">
        <div class="border-end" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4">
                <i class="fas fa-store me-2"></i> Sari-Sari POS
            </div>
            <div class="list-group list-group-flush">
                
                {{-- COMMON LINKS (Accessible to All) --}}
                <a href="{{ route('cashier.pos') }}" class="list-group-item list-group-item-action {{ request()->routeIs('cashier.pos') ? 'active' : '' }}">
                    <i class="fas fa-cash-register text-success"></i> Go to POS
                </a>

                {{-- ADMIN ONLY LINKS --}}
                @if(Auth::user()->role === 'admin')
                    
                    {{-- 1. MANAGEMENT SECTION (Fixed Visibility) --}}
                    <div class="sidebar-heading small text-uppercase text-white-50 mt-2">Management</div>

                    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    
                    <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('products.*') ? 'active' : '' }}">
                        <i class="fas fa-box-open"></i> Products
                    </a>

                    <a href="{{ route('inventory.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                        <i class="fas fa-warehouse"></i> Inventory
                    </a>

                    <a href="{{ route('purchases.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                        <i class="fas fa-truck-loading"></i> Restocking
                    </a>

                    {{-- 2. FINANCE & PEOPLE (Collapsible - Open Default) --}}
                    <div class="list-group-item submenu-toggle mt-2" data-bs-toggle="collapse" data-bs-target="#financeSubmenu">
                        <small class="text-uppercase">Finance & People</small>
                        <i class="fas fa-caret-down"></i>
                    </div>
                    <div class="collapse show" id="financeSubmenu">
                        <a href="{{ route('customers.index') }}" class="list-group-item list-group-item-action ps-4 {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i> Customers
                        </a>

                        <a href="{{ route('credits.index') }}" class="list-group-item list-group-item-action ps-4 {{ request()->routeIs('credits.*') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice-dollar"></i> Credits (Utang)
                        </a>

                        <a href="{{ route('suppliers.index') }}" class="list-group-item list-group-item-action ps-4 {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                            <i class="fas fa-truck"></i> Suppliers
                        </a>
                    </div>

                    {{-- 3. SYSTEM (Collapsible - Open Default) --}}
                    <div class="list-group-item submenu-toggle mt-2" data-bs-toggle="collapse" data-bs-target="#systemSubmenu">
                        <small class="text-uppercase">System</small>
                        <i class="fas fa-caret-down"></i>
                    </div>
                    <div class="collapse show" id="systemSubmenu">
                        <a href="{{ route('reports.index') }}" class="list-group-item list-group-item-action ps-4 {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i> Reports
                        </a>

                        <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action ps-4 {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="fas fa-user-cog"></i> Users
                        </a>

                        <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action ps-4 {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="fas fa-cogs"></i> Settings
                        </a>
                        
                        <li class="nav-item">
                            <a href="{{ route('admin.transaction_history') }}" class="list-group-item list-group-item-action ps-4 {{ request()->routeIs('transaction_history.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-history"></i>
                                <p>Transaction History</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.audit_logs') }}" class="list-group-item list-group-item-action ps-4 {{ request()->routeIs('audit_logs.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>Audit Logs</p>
                            </a>
                        </li>

                    </div>

                @endif
                
                {{-- LOGOUT --}}
                <form action="{{ route('logout') }}" method="POST" class="mt-auto border-top border-secondary">
                    @csrf
                    <button class="list-group-item list-group-item-action text-danger bg-dark py-3">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
                <div class="container-fluid">
                    <button class="btn btn-outline-secondary" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto mt-2 mt-lg-0 align-items-center">
                            
                            {{-- ALERTS (Only for Admin) --}}
                            @if(Auth::user()->role === 'admin')
                            <li class="nav-item dropdown me-3">
                                <a class="nav-link position-relative" href="#" id="alertsDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-bell fa-lg text-secondary"></i>
                                    @if($totalAlerts ?? 0 > 0)
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            {{ $totalAlerts }}
                                        </span>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="alertsDropdown">
                                    <li><h6 class="dropdown-header fw-bold">Notifications</h6></li>
                                    @if(($outOfStockCount ?? 0) > 0)
                                    <li><a class="dropdown-item text-danger" href="{{ route('products.index') }}"><small>• {{ $outOfStockCount }} Items Out of Stock</small></a></li>
                                    @endif
                                    @if(($lowStockCount ?? 0) > 0)
                                    <li><a class="dropdown-item text-warning" href="{{ route('products.index') }}"><small>• {{ $lowStockCount }} Items Low Stock</small></a></li>
                                    @endif
                                    @if(($overdueCount ?? 0) > 0)
                                    <li><a class="dropdown-item text-dark" href="{{ route('credits.index') }}"><small>• {{ $overdueCount }} Overdue Credits</small></a></li>
                                    @endif
                                </ul>
                            </li>
                            @endif

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle fw-bold" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }} 
                                    <span class="badge bg-secondary ms-1">{{ ucfirst(Auth::user()->role) }}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button class="dropdown-item text-danger">Logout</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', event => {
            const sidebarToggle = document.body.querySelector('#sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', event => {
                    event.preventDefault();
                    document.body.classList.toggle('sb-sidenav-toggled');
                });
            }
        });
    </script>
</body>
</html>