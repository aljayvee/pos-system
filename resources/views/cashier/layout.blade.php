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
<body>

    {{-- NAVBAR --}}
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm py-2 sticky-top">
        <div class="container-fluid">
            
            {{-- BRAND --}}
            <a class="navbar-brand fw-bold d-flex align-items-center me-auto" href="#">
                <div class="bg-warning text-dark rounded-3 d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                    <i class="fas fa-cash-register small"></i>
                </div>
                <span style="letter-spacing: -0.5px;">SariPOS</span>
            </a>

            {{-- MOBILE ADMIN LINK --}}
            @if(Auth::user()->role === 'admin')
                <a class="btn btn-outline-warning btn-sm fw-bold me-3 d-lg-none rounded-pill px-3" href="{{ route('admin.dashboard') }}">
                    Admin
                </a>
            @endif
            
            {{-- HAMBURGER --}}
            <button class="navbar-toggler border-0 p-1" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- MENU --}}
            <div class="collapse navbar-collapse" id="mobileMenu">
                
                {{-- MOBILE CONTROL PANEL --}}
                <div class="d-lg-none bg-white rounded-4 shadow-lg p-3 mt-3 border-top border-4 border-warning">
                    <div class="d-flex align-items-center mb-4 p-2 bg-light rounded-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                            <span class="fw-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">{{ Auth::user()->name }}</div>
                            <div class="small text-muted">{{ Auth::user()->email }}</div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button class="btn btn-light border py-2 text-start fw-bold text-secondary" onclick="openDebtorList()">
                            <i class="fas fa-hand-holding-usd me-2 text-danger"></i> Pay Debt
                        </button>
                        <button class="btn btn-light border py-2 text-start fw-bold text-secondary" onclick="openReturnModal()">
                            <i class="fas fa-undo me-2 text-warning"></i> Process Return
                        </button>

                        {{-- === ADDED: PROFILE SETTINGS BUTTON === --}}
                        <a href="{{ route('profile.edit', ['context' => 'cashier']) }}" class="btn btn-light border py-2 text-start fw-bold text-secondary">
                            <i class="fas fa-user-cog me-2 text-primary"></i> Profile Settings
                        </a>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-danger w-100 rounded-pill fw-bold">Logout</button>
                    </form>
                </div>

                {{-- DESKTOP MENU --}}
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 d-none d-lg-flex align-items-center gap-3">
                    @if(Auth::user()->role === 'admin')
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm fw-medium rounded-pill px-3 opacity-75 hover-opacity-100" href="{{ route('admin.dashboard') }}">
                            Back to Admin
                        </a>
                    </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white fw-medium d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
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
            </div>
        </div>
    </nav>

    <div>@yield('content')</div>

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