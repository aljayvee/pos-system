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

    {{-- COMPACT MOBILE NAVBAR --}}
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm p-0" style="background: #1e1b4b !important;">
        <div class="container-fluid py-1">
            {{-- BRAND (Height minimized) --}}
            <a class="navbar-brand fw-bold fs-6 fs-lg-4 d-flex align-items-center m-0" href="#">
                <i class="fas fa-cash-register me-2 text-warning small"></i> 
                <span>SariPOS</span>
            </a>
            
            {{-- TOGGLER (Compacted) --}}
            <button class="navbar-toggler border-0 p-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon" style="width: 1em; height: 1em;"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center mt-2 mt-lg-0">
                    
                    @if(Auth::user()->role === 'admin')
                        <li class="nav-item me-3">
                            <a class="btn btn-outline-light btn-sm fw-bold w-100 mb-2 mb-lg-0" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-arrow-left me-1"></i> Admin Panel
                            </a>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white fw-bold small" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="{{ route('profile.edit', ['context' => 'cashier']) }}">Profile</a></li>
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