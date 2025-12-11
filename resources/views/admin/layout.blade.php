<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { overflow-x: hidden; background-color: #f8f9fa; }
        
        /* Sidebar Styles */
        #sidebar-wrapper {
            min-height: 100vh;
            margin-left: -15rem;
            transition: margin 0.25s ease-out;
            background-color: #212529; /* Dark Sidebar */
            color: white;
        }
        
        #sidebar-wrapper .sidebar-heading {
            padding: 0.875rem 1.25rem;
            font-size: 1.2rem;
            font-weight: bold;
            background-color: #1a1e21; /* Slightly darker header */
            border-bottom: 1px solid #495057;
        }
        
        #sidebar-wrapper .list-group { width: 15rem; }
        
        #page-content-wrapper { min-width: 100vw; transition: margin 0.25s ease-out; }
        
        /* Sidebar Toggled State */
        body.sb-sidenav-toggled #sidebar-wrapper { margin-left: 0; }
        
        /* Links */
        .list-group-item {
            border: none;
            padding: 15px 20px;
            background-color: #212529;
            color: #d1d5db;
        }
        .list-group-item:hover {
            background-color: #343a40;
            color: #fff;
        }
        .list-group-item.active {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
        }
        .list-group-item i { width: 25px; }

        /* Responsive Adjustments */
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
                <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="{{ route('categories.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i> Categories
                </a>
                <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="fas fa-box-open"></i> Products
                </a>

                {{-- NEW: Inventory Link Added Here --}}
                <a href="{{ route('inventory.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <i class="fas fa-warehouse"></i> Inventory
                </a>

                {{-- NEW: Purchases / Stock In Link --}}
                <a href="{{ route('purchases.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                    <i class="fas fa-truck-loading"></i> Stock In / Purchases
                </a>
                
                <a href="{{ route('customers.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Customers
                </a>

                {{-- MERGED CREDITS MENU --}}
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" 
                   href="#creditsSubmenu" data-bs-toggle="collapse" role="button" 
                   aria-expanded="{{ request()->routeIs('credits.*') ? 'true' : 'false' }}">
                    <span><i class="fas fa-file-invoice-dollar"></i> Credits (Utang)</span>
                    <i class="fas fa-caret-down"></i>
                </a>
                
                <div class="collapse {{ request()->routeIs('credits.*') ? 'show' : '' }}" id="creditsSubmenu" style="background-color: #1a1e21;">
                    {{-- 1. Outstanding List --}}
                    <a href="{{ route('credits.index') }}" 
                       class="list-group-item list-group-item-action ps-5 {{ request()->routeIs('credits.index') ? 'active' : '' }}" 
                       style="background-color: transparent;">
                        <i class="fas fa-list-ul me-2"></i> Outstanding
                    </a>
                    
                    {{-- 2. Payment History --}}
                    <a href="{{ route('credits.logs') }}" 
                       class="list-group-item list-group-item-action ps-5 {{ request()->routeIs('credits.logs') ? 'active' : '' }}" 
                       style="background-color: transparent;">
                        <i class="fas fa-history me-2"></i> Payment History
                    </a>
                </div>

                {{-- NEW REPORTS BUTTON ADDED HERE --}}
                <a href="{{ route('reports.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Sales Reports
                </a>
                
                <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-user-cog"></i> User Management
                </a>

                {{-- NEW SETTINGS LINK --}}
                <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cogs"></i> Settings
                </a>
                
                <form action="{{ route('logout') }}" method="POST" class="mt-auto">
                
                <form action="{{ route('logout') }}" method="POST" class="mt-auto">
                    @csrf
                    <button class="list-group-item list-group-item-action text-danger bg-dark">
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
                        <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle fw-bold" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    {{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#">Profile</a></li>
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