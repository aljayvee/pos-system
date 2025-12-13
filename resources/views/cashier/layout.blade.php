<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - Cashier</title>
    
    {{-- 1. Link the Manifest for PWA Support --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4f46e5">
    <link rel="apple-touch-icon" href="https://cdn-icons-png.flaticon.com/512/3081/3081559.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body { background-color: #f3f4f6; } /* Matches new POS design */
    </style>
</head>
<body>

    {{-- MOBILE-OPTIMIZED NAVBAR --}}
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm p-0" style="background: #1e1b4b !important;">
        <div class="container-fluid py-1">
            
            {{-- 1. BRAND --}}
            <a class="navbar-brand fw-bold fs-6 fs-lg-4 d-flex align-items-center m-0 me-auto" href="#">
                <i class="fas fa-cash-register me-2 text-warning small"></i> 
                <span>SariPOS</span>
            </a>

            {{-- 2. ADMIN BUTTON (Right-side, Outside Hamburger) --}}
            @if(Auth::user()->role === 'admin')
                <a class="btn btn-outline-warning btn-sm fw-bold me-2 py-1 px-2" style="font-size: 0.75rem;" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-arrow-left me-1"></i> Admin
                </a>
            @endif
            
            {{-- 3. HAMBURGER TOGGLER --}}
            <button class="navbar-toggler border-0 p-1" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu">
                <span class="navbar-toggler-icon" style="width: 1.2em; height: 1.2em;"></span>
            </button>

            {{-- 4. COLLAPSIBLE MENU AREA --}}
            <div class="collapse navbar-collapse" id="mobileMenu">
                
                {{-- A. MOBILE "CONTROL PANEL" VIEW (Visible only on Mobile) --}}
                <div class="d-lg-none bg-white rounded-3 shadow-lg p-3 mt-3 position-relative" style="z-index: 1050; border-top: 4px solid #f59e0b;">
                    
                    {{-- Visual Arrow pointing up --}}
                    <div class="position-absolute top-0 end-0 me-2 mt-n2" style="width: 0; height: 0; border-left: 10px solid transparent; border-right: 10px solid transparent; border-bottom: 10px solid #f59e0b; transform: translateY(-100%);"></div>

                    {{-- 1. USER PROFILE ROW --}}
                    <div class="d-flex align-items-center border-bottom pb-3 mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Store Owner</small>
                            <div class="fw-bolder text-dark fs-5 lh-1">{{ Auth::user()->name }}</div>
                            <div class="small text-secondary mt-1">{{ Auth::user()->email }}</div>
                        </div>
                    </div>

                    {{-- 2. ACTION BUTTONS GRID (Big Buttons for Touch) --}}
                    <label class="small text-muted fw-bold text-uppercase mb-2">Transactions</label>
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <button class="btn btn-outline-danger w-100 p-2 border-2 fw-bold d-flex flex-column align-items-center justify-content-center gap-1 shadow-sm" style="height: 90px;" onclick="openDebtorList()">
                                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mb-1" style="width: 32px; height: 32px;"><i class="fas fa-hand-holding-usd"></i></div>
                                <span class="small">Pay Debt</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-warning w-100 p-2 border-2 fw-bold d-flex flex-column align-items-center justify-content-center gap-1 text-dark shadow-sm" style="height: 90px;" onclick="openReturnModal()">
                                <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center mb-1" style="width: 32px; height: 32px;"><i class="fas fa-undo"></i></div>
                                <span class="small">Return</span>
                            </button>
                        </div>
                        
                        {{-- Optional Report Button --}}
                        @if(\App\Models\Setting::where('key', 'enable_tax')->value('value') == '1')
                        <div class="col-12">
                            <a href="{{ route('cashier.reading', 'x') }}" target="_blank" class="btn btn-light w-100 py-2 border fw-bold d-flex align-items-center justify-content-center gap-2 text-secondary">
                                <i class="fas fa-print"></i> Print X-Reading Report
                            </a>
                        </div>
                        @endif
                    </div>

                    {{-- 3. SETTINGS LIST --}}
                    <label class="small text-muted fw-bold text-uppercase mb-2">Account</label>
                    <div class="list-group list-group-flush rounded-3 border">
                        <a href="{{ route('profile.edit', ['context' => 'cashier']) }}" class="list-group-item list-group-item-action py-3 d-flex align-items-center fw-bold text-dark">
                            <i class="fas fa-cog fa-fw me-3 text-secondary"></i> Profile Settings
                            <i class="fas fa-chevron-right ms-auto text-muted small"></i>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="d-block w-100 m-0">
                            @csrf
                            <button class="list-group-item list-group-item-action py-3 d-flex align-items-center fw-bold text-danger w-100">
                                <i class="fas fa-sign-out-alt fa-fw me-3"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>

                {{-- B. DESKTOP STANDARD MENU (Hidden on Mobile) --}}
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 d-none d-lg-flex align-items-center">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white fw-bold small" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="{{ route('profile.edit', ['context' => 'cashier']) }}">Profile Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item text-danger fw-bold">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- Content Area --}}
    <div>
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- 2. Register Service Worker --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('SW Registered!', reg.scope))
                    .catch(err => console.log('SW Failed:', err));
            });
        }
    </script>
</body>
</html>