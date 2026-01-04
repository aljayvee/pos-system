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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- VITE ASSETS --}}
    @vite(['resources/css/app.css', 'resources/css/premium-ui.css', 'resources/js/app.js'])

    <style>
        /* [FIX] GLOBAL ZOOM 80% FORMULA (Matches Admin Layout) */
        html {
            background: linear-gradient(135deg, var(--bg-gradient-start), var(--bg-gradient-end));
            min-height: 100vh;
            height: 100%;
        }

        body {
            zoom: 80%;
            /* background is on html to cover zoom whitespace */
            height: 125vh;
            /* Formula: 100vh / 0.8 zoom = 125vh */
            overflow: hidden;
            /* Lock global scroll */
            display: flex;
            flex-direction: column;
            margin: 0;
        }

        /* Additional Layout Specifics */
        .layout-wrapper {
            display: flex;
            height: 100%;
            /* Fill body */
            width: 100%;
            overflow: hidden;
            /* background moved to html/body */
        }

        /* Sidebar Styles */
        .app-sidebar {
            width: 80px;
            /* Collapsed by default for minimal icons */
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            z-index: 1000;
            transition: width 0.3s ease;
        }

        .app-sidebar:hover {
            width: 240px;
            /* Expand on hover */
        }

        .sidebar-brand {
            margin-bottom: 40px;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .sidebar-brand .text {
            display: none;
            margin-left: 10px;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .app-sidebar:hover .sidebar-brand .text {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            padding: 0 10px;
        }

        .nav-item-link {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            border-radius: 12px;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.2s;
            position: relative;
        }

        .nav-item-link:hover,
        .nav-item-link.active {
            background: rgba(255, 255, 255, 0.5);
            /* Glass hover */
            color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .nav-item-link i {
            font-size: 1.4rem;
            min-width: 24px;
            text-align: center;
        }

        .nav-item-link span {
            display: none;
            margin-left: 15px;
            font-weight: 500;
            white-space: nowrap;
        }

        .app-sidebar:hover .nav-item-link {
            justify-content: flex-start;
            padding-left: 20px;
        }

        .app-sidebar:hover .nav-item-link span {
            display: inline;
            animation: fadeIn 0.2s ease;
        }

        /* Active Indicator */
        .nav-item-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 60%;
            width: 3px;
            background: var(--primary-color);
            border-radius: 0 4px 4px 0;
            display: none;
        }

        .app-sidebar:hover .nav-item-link.active::before {
            display: block;
        }

        /* Main Content */
        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        /* Top Bar */
        .top-bar {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            /* background: transparent; */
            /* Let gradient show */
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 8px 16px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .user-profile:hover {
            background: #fff;
            box-shadow: var(--glass-shadow);
        }

        .notification-bell {
            position: relative;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 50%;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .notification-bell:hover {
            color: var(--primary-color);
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateX(-5px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Mobile overrides handled in standard media queries */
        @media (max-width: 991px) {
            .app-sidebar {
                display: none;
            }

            .layout-wrapper {
                flex-direction: column;
            }

            .top-bar {
                display: none;
                /* Mobile has its own header in index.blade.php */
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Poll for login requests
        document.addEventListener('DOMContentLoaded', () => {
            if (window.Echo) {
                // Listen on the same channel as Admin
                window.Echo.private('admin-notifications')
                    .listen('.LoginRequestCreated', (e) => {
                        console.log('Login Request Received:', e.details);
                        showConsentModal(e.details);
                    });
            }
        });

        let isModalOpen = false;

        function showConsentModal(details) {
            if (isModalOpen) return;
            isModalOpen = true;

            Swal.fire({
                title: 'New Login Detected',
                html: `
                    <div class="text-start text-sm">
                        <p>A new device is trying to log in:</p>
                        <ul class="list-unstyled ms-2 mt-2">
                             <li><i class="fas fa-network-wired me-2"></i><strong>IP:</strong> ${details.ip}</li>
                             <li><i class="fas fa-desktop me-2"></i><strong>Device:</strong> ${details.device}</li>
                        </ul>
                        <p class="mt-4 fw-bold text-danger">Do you want to allow this?</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Allow',
                cancelButtonText: 'No, Block',
                allowOutsideClick: false,
                backdrop: `rgba(0,0,0,0.8)`
            }).then((result) => {
                isModalOpen = false;
                let decision = result.isConfirmed ? 'approve' : 'deny';

                axios.post('{{ route("auth.resolve_request") }}', {
                    decision: decision,
                    request_id: details.request_id,
                    _token: '{{ csrf_token() }}'
                }).then(res => {
                    if (res.data.action === 'logout_self') {
                        window.location.reload();
                    } else {
                        Swal.fire('Blocked', 'The login request was denied.', 'success');
                    }
                }).catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'Could not process request', 'error');
                });
            });
        }
    </script>

    <script>
        // Theme Manager Logic
        const CashierThemeManager = {
            init() {
                const storedTheme = localStorage.getItem('theme_cashier');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (storedTheme === 'dark' || (!storedTheme && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                this.updateIcons();
            },
            toggle() {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme_cashier', isDark ? 'dark' : 'light');
                this.updateIcons();
            },
            updateIcons() {
                const isDark = document.documentElement.classList.contains('dark');
                const icons = document.querySelectorAll('.theme-toggle-icon');
                icons.forEach(icon => {
                    if (isDark) {
                        icon.classList.remove('fa-moon');
                        icon.classList.add('fa-sun');
                    } else {
                        icon.classList.remove('fa-sun');
                        icon.classList.add('fa-moon');
                    }
                });
            }
        };

        // Initialize immediately to prevent flash
        CashierThemeManager.init();

        document.addEventListener('DOMContentLoaded', () => {
            CashierThemeManager.updateIcons();
        });
    </script>
</head>

<body class="antialiased">

    <div class="layout-wrapper">
        {{-- DESKTOP SIDEBAR --}}
        <aside class="app-sidebar d-none d-lg-flex">
            <div class="sidebar-brand">
                <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center shadow-lg"
                    style="width: 40px; height: 40px;">
                    <i class="fas fa-cash-register"></i>
                </div>
                <span class="text">VeraPOS</span>
            </div>

            <nav class="sidebar-nav">
                <a href="#" class="nav-item-link active" title="POS Terminal">
                    <i class="fas fa-store"></i>
                    <span>Terminal</span>
                </a>

                @if(Auth::user()->role !== 'cashier')
                    <a href="{{ route('admin.dashboard') }}" class="nav-item-link" title="Admin Dashboard">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                @endif


            </nav>
        </aside>

        {{-- MAIN CONTENT AREA --}}
        <main class="main-content">

            {{-- DESKTOP TOP BAR --}}
            <header class="top-bar d-none d-lg-flex">
                <div>
                    {{-- Breadcrumbs or Page Title could go here --}}
                    <h5 class="m-0 fw-bold text-dark opacity-75">Cashier Terminal</h5>
                </div>

                <div class="d-flex align-items-center gap-3">
                    {{-- CLOSE REGISTER (Desktop) --}}
                    @if(request()->routeIs('cashier.pos') && isset($registerLogsEnabled) && $registerLogsEnabled == '1')
                        <button class="btn btn-danger btn-sm fw-bold px-3 rounded-pill shadow-sm"
                            id="btn-close-register-desktop" onclick="showCloseRegisterModal()" title="Close Register">
                            <i class="fas fa-store-slash me-2"></i>Close Register
                        </button>
                    @endif

                    {{-- Theme Toggle --}}
                    <button class="notification-bell border-0" onclick="CashierThemeManager.toggle()"
                        title="Toggle Dark Mode">
                        <i class="fas fa-moon theme-toggle-icon"></i>
                    </button>

                    {{-- Notifications --}}
                    <div class="notification-bell">
                        <i class="fas fa-bell"></i>
                        <span
                            class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"
                            style="width:10px; height:10px;"></span>
                    </div>

                    {{-- User Profile --}}
                    <div class="dropdown">
                        <div class="user-profile" data-bs-toggle="dropdown">
                            @if(Auth::user()->profile_photo_path)
                                <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" class="rounded-circle"
                                    style="width: 32px; height: 32px; object-fit: cover;">
                            @else
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 32px; height: 32px; font-weight: bold;">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                            @endif
                            <div class="d-flex flex-column" style="line-height: 1.2;">
                                <span class="fw-bold text-dark small">{{ Auth::user()->name }}</span>
                                <span class="text-muted"
                                    style="font-size: 0.7rem;">{{ ucfirst(Auth::user()->role) }}</span>
                            </div>
                            <i class="fas fa-chevron-down text-muted ms-2" style="font-size: 0.8rem;"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-2">
                            <li><a class="dropdown-item rounded-3"
                                    href="{{ route('profile.edit', ['context' => 'cashier']) }}">Profile Settings</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item rounded-3 text-danger fw-bold">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            {{-- CONTENT INJECTION --}}
            <div class="flex-grow-1 overflow-auto position-relative">
                @yield('content')
            </div>

        </main>
    </div>

    {{-- MOBILE OFFCANVAS NAV (Preserved) --}}
    <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="mobileNavDrawer"
        style="width: 80%; max-width: 320px;">
        {{-- ... Existing Mobile Nav Content ... --}}
        <div class="offcanvas-header bg-white border-bottom p-4 d-flex align-items-center justify-content-start gap-3">
            @if(Auth::user()->profile_photo_path)
                <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}"
                    class="rounded-circle border shadow-sm flex-shrink-0"
                    style="width: 50px; height: 50px; object-fit: cover;">
            @else
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0"
                    style="width: 50px; height: 50px; font-size: 1.25rem;">
                    <span class="fw-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
            @endif
            <div class="d-flex flex-column overflow-hidden">
                <h6 class="fw-bold mb-1 text-truncate text-dark">{{ Auth::user()->name }}</h6>
                <small class="text-muted text-truncate">{{ Auth::user()->email }}</small>
            </div>
        </div>
        <div class="offcanvas-body p-0 bg-light d-flex flex-column">
            <div class="list-group list-group-flush bg-transparent mt-2">
                <button onclick="CashierThemeManager.toggle()"
                    class="list-group-item list-group-item-action py-3 px-4 border-0 bg-transparent d-flex align-items-center gap-3">
                    <i class="fas fa-moon theme-toggle-icon text-secondary w-25px text-center"></i>
                    <span class="fw-medium text-dark">Switch Theme</span>
                </button>

                @if(Auth::user()->role !== 'cashier')
                    <a href="{{ route('admin.dashboard') }}"
                        class="list-group-item list-group-item-action py-3 px-4 border-0 bg-transparent d-flex align-items-center gap-3">
                        <i class="fas fa-chart-pie text-secondary w-25px text-center"></i>
                        <span class="fw-medium text-dark">Dashboard</span>
                    </a>
                @endif

                @if(request()->routeIs('cashier.pos'))
                    <div class="border-top my-2 mx-3"></div>
                    <small class="px-4 text-muted fw-bold mb-1" style="font-size: 0.7rem;">ACTIONS</small>
                    <button onclick="requestAdminAuth(openDebtorList)"
                        class="list-group-item list-group-item-action py-3 px-4 border-0 bg-transparent d-flex align-items-center gap-3">
                        <i class="fas fa-book text-danger w-25px text-center"></i>
                        <span class="fw-medium text-dark">Pay Debt / Debtors</span>
                    </button>
                    <button onclick="requestAdminAuth(openReturnModal)"
                        class="list-group-item list-group-item-action py-3 px-4 border-0 bg-transparent d-flex align-items-center gap-3">
                        <i class="fas fa-undo-alt text-warning w-25px text-center"></i>
                        <span class="fw-medium text-dark">Return Items</span>
                    </button>
                    {{-- X-READING Mobile --}}
                    @if(config('safety_flag_features.bir_tax_compliance'))
                        <button
                            onclick="requestAdminAuth(() => window.open('/cashier/reading/x', '_blank', 'width=400,height=600'))"
                            class="list-group-item list-group-item-action py-3 px-4 border-0 bg-transparent d-flex align-items-center gap-3">
                            <i class="fas fa-file-invoice-dollar text-primary w-25px text-center"></i>
                            <span class="fw-medium text-dark">X-Reading Report</span>
                        </button>
                    @endif
                    @if(isset($registerLogsEnabled) && $registerLogsEnabled == '1')
                        <button onclick="showCloseRegisterModal()" id="btn-close-register-mobile"
                            class="list-group-item list-group-item-action py-3 px-4 border-0 bg-transparent d-flex align-items-center gap-3">
                            <i class="fas fa-store-slash text-danger w-25px text-center"></i>
                            <span class="fw-medium text-danger">Close Register</span>
                        </button>
                    @endif
                    <div class="border-bottom my-2 mx-3"></div>
                @endif

                <a href="{{ route('profile.edit', ['context' => 'cashier']) }}"
                    class="list-group-item list-group-item-action py-3 px-4 border-0 bg-transparent d-flex align-items-center gap-3">
                    <i class="fas fa-user-cog text-secondary w-25px text-center"></i>
                    <span class="fw-medium text-dark">Profile Settings</span>
                </a>

                {{-- Add other mobile links here if needed --}}

                <form action="{{ route('logout') }}" method="POST" class="w-100 mt-auto">
                    @csrf
                    <button
                        class="list-group-item list-group-item-action py-3 px-4 border-0 bg-transparent d-flex align-items-center gap-3 text-danger w-100">
                        <i class="fas fa-sign-out-alt w-25px text-center"></i>
                        <span class="fw-bold">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @stack('modals')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW Failed'));
            });
        }
    </script>
    </script>
    @stack('scripts')
</body>

</html>