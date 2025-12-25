<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - Cashier</title>
    
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4f46e5">
    <link rel="apple-touch-icon" href="https://cdn-icons-png.flaticon.com/512/3081/3081559.png">

    {{-- Fonts & Icons --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --bg-color: #f8fafc;
            --surface-color: #ffffff;
        }
        body { 
            background-color: var(--bg-color); 
            font-family: 'Inter', sans-serif; 
            -webkit-font-smoothing: antialiased;
        }
        .navbar {
            background: #1e1b4b !important; /* Deep Indigo */
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Poll for login requests
    setInterval(() => {
        axios.get('{{ route("auth.check_requests") }}')
            .then(response => {
                if (response.data.has_request) {
                    // We pass the whole object including request_id
                    showConsentModal(response.data.details);
                }
            });
    }, 4000);

    let isModalOpen = false;

    function showConsentModal(details) {
        if (isModalOpen) return;
        isModalOpen = true;

        Swal.fire({
            title: 'New Login Detected',
            html: `
                <div class="text-left text-sm">
                    <p>A new device is trying to log in:</p>
                    <ul class="list-disc ml-5 mt-2">
                        <li><strong>IP:</strong> ${details.ip}</li>
                        <li><strong>Device:</strong> ${details.device}</li>
                    </ul>
                    <p class="mt-4 font-bold text-red-600">Do you want to allow this?</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Log them in',
            cancelButtonText: 'No, Block them',
            allowOutsideClick: false
        }).then((result) => {
            isModalOpen = false;
            let decision = result.isConfirmed ? 'approve' : 'deny';

            axios.post('{{ route("auth.resolve_request") }}', {
                decision: decision,
                request_id: details.request_id, // <--- CRITICAL: Pass the ID back
                _token: '{{ csrf_token() }}'
            }).then(res => {
                if (res.data.action === 'logout_self') {
                    window.location.reload(); 
                } else {
                    Swal.fire('Blocked', 'The login request was denied.', 'success');
                }
            });
        });
    }
</script>
<body class="d-flex flex-column h-100">

    {{-- NAVBAR --}}
    <nav class="navbar navbar-dark shadow-sm py-2 sticky-top d-none d-lg-block">
        <div class="container-fluid d-flex align-items-center justify-content-between">
            
            {{-- BRAND --}}
            <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
                <div class="bg-warning text-dark rounded-3 d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                    <i class="fas fa-cash-register small"></i>
                </div>
                <span style="letter-spacing: -0.5px;">VeraPOS</span>
            </a>

            {{-- DESKTOP MENU (Hidden on Mobile) --}}
            <ul class="navbar-nav ms-auto d-none d-lg-flex flex-row align-items-center gap-3">
                @if(Auth::user()->role !== 'cashier')
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm fw-medium rounded-pill px-3 opacity-75 hover-opacity-100" href="{{ route('admin.dashboard') }}">
                        Back to Admin
                    </a>
                </li>
                @endif
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white fw-medium d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <span class="small fw-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-2">
                        <li><a class="dropdown-item rounded-3" href="{{ route('profile.edit', ['context' => 'cashier']) }}">Profile Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item text-danger fw-bold rounded-3">Logout</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>

            {{-- HAMBURGER (Mobile Only) --}}
            <button class="navbar-toggler border-0 p-1 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNavDrawer">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    {{-- MOBILE NAVIGATION DRAWER (Offcanvas) --}}
    <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="mobileNavDrawer" style="width: 80%; max-width: 320px;">
        
        {{-- Drawer Header: User Profile --}}
        <div class="offcanvas-header bg-white border-bottom p-4 d-flex align-items-center justify-content-start gap-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 50px; height: 50px; font-size: 1.25rem;">
                <span class="fw-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
            </div>
            <div class="d-flex flex-column overflow-hidden">
                <h6 class="fw-bold mb-1 text-truncate text-dark">{{ Auth::user()->name }}</h6>
                <small class="text-muted text-truncate">{{ Auth::user()->email }}</small>
            </div>
            
        </div>

        {{-- Drawer Body: Navigation Links --}}
        <div class="offcanvas-body p-0 bg-light d-flex flex-column">
            <div class="list-group list-group-flush bg-transparent mt-2">
                
                <a href="{{ route('profile.edit', ['context' => 'cashier']) }}" class="list-group-item list-group-item-action py-3 px-4 border-0 bg-transparent d-flex align-items-center gap-3">
                    <i class="fas fa-user-cog text-secondary w-25px text-center"></i>
                    <span class="fw-medium text-dark">Profile Settings</span>
                </a>

                <button class="list-group-item list-group-item-action py-3 px-4 border-0 bg-transparent d-flex align-items-center gap-3" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('mobileNavDrawer')).hide(); requestAdminAuth(openDebtorList);">
                   <i class="fas fa-hand-holding-usd text-secondary w-25px text-center"></i>
                    <span class="fw-medium text-dark">Pay Debt</span>
                </button>
                
                <button class="list-group-item list-group-item-action py-3 px-4 border-0 bg-transparent d-flex align-items-center gap-3" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('mobileNavDrawer')).hide(); requestAdminAuth(openReturnModal);">
                    <i class="fas fa-undo text-secondary w-25px text-center"></i>
                    <span class="fw-medium text-dark">Return Items</span>
                </button>

            </div>

             <div class="my-2 border-top"></div>

            <div class="list-group list-group-flush bg-transparent">
                 @if(Auth::user()->role !== 'cashier')
                <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action py-3 px-4 border-0 bg-transparent d-flex align-items-center gap-3">
                    <i class="fas fa-th-large text-secondary w-25px text-center"></i>
                    <span class="fw-medium text-dark">Back to Dashboard</span>
                </a>
                <div class="my-2 border-top"></div>
                @endif
                
                 <form action="{{ route('logout') }}" method="POST" class="w-100">
                    @csrf
                     <button class="list-group-item list-group-item-action py-3 px-4 border-0 bg-transparent d-flex align-items-center gap-3 text-danger w-100">
                        <i class="fas fa-sign-out-alt w-25px text-center"></i>
                        <span class="fw-bold">Logout</span>
                    </button>
                </form>

            </div>

             {{-- Version/Footer --}}
            <div class="mt-auto p-4 text-center text-muted opacity-50 small">
                VeraPOS v{{ config('version.full') }}
            </div>
        </div>
    </div>

    <div class="flex-grow-1 overflow-hidden">@yield('content')</div>

    @stack('modals')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW Failed'));
            });
        }
    </script>
</body>
</html>