<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $pageTitle ?? 'POS System' }}</title>
    
    {{-- CSS Libraries --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('styles')
</head>

<body class="bg-gray-100">
    <div id="app" class="wrapper d-flex align-items-stretch">
        
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-cash-register"></i> Cashier POS</h3>
            </div>

            <ul class="list-unstyled components">
                <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>

                <p class="menu-label">Inventory</p>
                <li class="{{ request()->routeIs('inventory.index') ? 'active' : '' }}">
                    <a href="{{ route('inventory.index') }}"><i class="fas fa-boxes"></i> Inventory Overview</a>
                </li>
                <li class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <a href="{{ route('products.index') }}"><i class="fas fa-box-open"></i> Product</a>
                </li>
                <li class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <a href="{{ route('categories.index') }}"><i class="fas fa-tags"></i> Category</a>
                </li>
                <li class="{{ request()->routeIs('inventory.history') ? 'active' : '' }}">
                    <a href="{{ route('inventory.history') }}"><i class="fas fa-layer-group"></i> Stock History</a>
                </li>
                <li class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                    <a href="{{ route('purchases.index') }}"><i class="fas fa-truck-loading"></i> Restocking</a>
                </li>

                <p class="menu-label">People</p>
                <li class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <a href="{{ route('customers.index') }}"><i class="fas fa-users"></i> Customers</a>
                </li>
                <li class="{{ request()->routeIs('credits.*') ? 'active' : '' }}">
                    <a href="{{ route('credits.index') }}"><i class="fas fa-file-invoice-dollar"></i> Credits</a>
                </li>
                <li class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <a href="{{ route('suppliers.index') }}"><i class="fas fa-truck"></i> Suppliers</a>
                </li>

                <p class="menu-label">System</p>
                <li class="{{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                    <a href="{{ route('transactions.index') }}"><i class="fas fa-history"></i> Transaction History</a>
                </li>
                <li class="{{ request()->routeIs('logs.index') ? 'active' : '' }}">
                    <a href="{{ route('logs.index') }}"><i class="fas fa-clipboard-list"></i> Audit Logs</a>
                </li>
                <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}"><i class="fas fa-user-shield"></i> User Management</a>
                </li>
                <li class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <a href="{{ route('settings.index') }}"><i class="fas fa-cog"></i> Settings</a>
                </li>
                
                <li>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <a href="#" onclick="this.closest('form').submit()" class="text-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </form>
                </li>
            </ul>
        </nav>

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4 d-md-none">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-primary">
                        <i class="fas fa-bars"></i> Menu
                    </button>
                </div>
            </nav>

            @yield('content')
        </div>

        <div class="overlay"></div>

    </div>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const sidebarCollapseBtn = document.getElementById('sidebarCollapse');
            const overlay = document.querySelector('.overlay');

            function toggleSidebar() {
                sidebar.classList.toggle('active');
                if(overlay) overlay.classList.toggle('active');
            }

            if(sidebarCollapseBtn) {
                sidebarCollapseBtn.addEventListener('click', toggleSidebar);
            }

            if(overlay) {
                overlay.addEventListener('click', toggleSidebar);
            }
        });
    </script>
</body>
</html>