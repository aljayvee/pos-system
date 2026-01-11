<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $pageTitle ?? 'VERAPOS' }}</title>

    {{-- CSS Libraries --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Vite Assets (Loads your Vue Component) --}}
    @vite(['resources/css/app.css', 'resources/css/premium-ui.css', 'resources/js/app.js'])

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('/') }}">

    <style>
        button:focus {
            outline: none !important;
        }

        /* FIX: Apply background to html to cover zoom whitespace */
        html {
            background-color: #f1f5f9;
            /* Match Bootstrap's bg-body (slate-100) */
            min-height: 100vh;
            height: 100%;
        }

        /* GLOBAL ZOOM 80% */
        body {
            zoom: 80%;
            background-color: #f1f5f9;
            /* Match Bootstrap's bg-body (slate-100) */
            height: 125vh;
            /* Fixed height for independent scrolling */
            overflow: hidden;
            /* Lock global scroll */
            display: flex;
            flex-direction: column;
            margin: 0;
        }

        #app {
            flex: 1;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        #global-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            /* Slightly lighter for glass effect */
            backdrop-filter: blur(10px);
            /* Glassmorphism */
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: white;
            font-family: 'Inter', sans-serif;
        }

        .loading-text {
            margin-top: 25px;
            font-size: 1rem;
            font-weight: 300;
            letter-spacing: 2px;
            animation: pulse 1.5s ease-in-out infinite;
            text-transform: uppercase;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 0.5;
                transform: scale(0.98);
            }

            50% {
                opacity: 1;
                transform: scale(1);
            }
        @keyframes pulse {
            0%, 100% { opacity: 0.5; transform: scale(0.98); }
            50% { opacity: 1; transform: scale(1); }
        }

        /* Skeleton Loading Styles */
        #skeleton-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #f1f5f9; /* Match body bg */
            z-index: 9998; /* Just below global overlay */
            display: none;
            padding: 20px;
            box-sizing: border-box;
        }

        .skeleton-header {
            height: 60px;
            width: 100%;
            background: #fff;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .skeleton-content {
            display: flex;
            gap: 20px;
            height: calc(100% - 80px);
        }

        .skeleton-sidebar {
            width: 250px;
            height: 100%;
            background: #fff;
            border-radius: 8px;
            display: none; /* Hidden on mobile by default */
        }
        
        @media (min-width: 992px) {
            .skeleton-sidebar { display: block; }
        }

        .skeleton-main {
            flex: 1;
            background: #fff;
            border-radius: 8px;
            height: 100%;
        }

        .skeleton-anim {
            background: linear-gradient(90deg, #e0e0e0 25%, #f0f0f0 50%, #e0e0e0 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
        }

        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
</head>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Poll for login requests
    // Listen for real-time login requests
    document.addEventListener('DOMContentLoaded', () => {
        if (window.Echo) {
            window.Echo.private('admin-notifications')
                .listen('.LoginRequestCreated', (e) => {
                    console.log('Login Request Received:', e.details);
                    showConsentModal(e.details);
                });
        } else {
            console.error('Laravel Echo not loaded.');
        }
    });



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
                    window.location.reload()
                        ;
                } else {
                    Swal.fire('Blocked', 'The login request was denied.', 'success');
                }
            });
        });
    }
</script>

<body class="bg-light">
    <div id="app">
        <admin-layout user-name="{{ Auth::user()->name }}" user-role="{{ Auth::user()->role }}"
            :user-permissions="{{ json_encode(Auth::user()->effective_permissions) }}"
            user-photo="{{ Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : '' }}"
            page-title="{{ $pageTitle ?? 'Dashboard' }}" csrf-token="{{ csrf_token() }}"
            :out-of-stock="{{ $outOfStockCount ?? 0 }}" :low-stock="{{ $lowStockCount ?? 0 }}"
            :enable-register-logs="{{ \App\Models\Setting::where('key', 'enable_register_logs')->value('value') ?? 0 }}"
            :enable-bir-compliance="{{ config('safety_flag_features.bir_tax_compliance') ? 'true' : 'false' }}"
            :system-mode="'{{ \App\Models\Setting::where('key', 'system_mode')->value('value') ?? 'single' }}'">
            @yield('content')

        </admin-layout>
        <offline-indicator></offline-indicator>
    </div>

    @stack('modals')

    <!-- Global Loading Overlay (Lottie) -->
    <div id="global-loading-overlay">
        <lottie-player src="https://assets5.lottiefiles.com/packages/lf20_t9gkkhz4.json" background="transparent" speed="1" style="width: 200px; height: 200px;" loop autoplay></lottie-player>
        <div class="loading-text" style="margin-top: -40px;">Loading, please wait...</div>
    </div>

    <!-- Skeleton Loading Overlay -->
    <div id="skeleton-loading-overlay">
        <div class="skeleton-header skeleton-anim"></div>
        <div class="skeleton-content">
            <div class="skeleton-sidebar skeleton-anim"></div>
            <div class="skeleton-main skeleton-anim"></div>
        </div>
    </div>

    {{-- 1. ADD THIS: Bootstrap JS Bundle (Required for Modals) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- OPTIMIZATION: Instant Page Loads (Prefetch on hover) --}}
    <script src="//instant.page/5.2.0" type="module"
        integrity="sha384-jnZyxPjiipYXnSU0ygqeac2q7CVYMbh84q0uHVRRxEtvFPiQYbXWUorga2aqZJ0z"
        crossorigin="anonymous"></script>

    @stack('scripts')

    {{-- 2. ADD THIS: Flash Message Bridge to Vue --}}
    <script>
        window.laravel_flash = {!! json_encode([
    'success' => session('success'),
    'error' => session('error'),
    'warning' => session('warning'),
    'info' => session('info')
]) !!};

        // Loading Overlay Logic
        document.addEventListener('DOMContentLoaded', function () {
            const globalOverlay = document.getElementById('global-loading-overlay');
            const skeletonOverlay = document.getElementById('skeleton-loading-overlay');

            function showLoading(type = 'global') {
                if (type === 'skeleton') {
                    skeletonOverlay.style.display = 'block';
                } else {
                    globalOverlay.style.display = 'flex';
                }
            }

            // 1. Form Submissions
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function (e) {
                    // Check if the form is invalid (standard HTML validation)
                    if (!form.checkValidity()) {
                        return;
                    }

                    // Check if default was prevented (e.g. by onsubmit="return confirm(...)")
                    if (e.defaultPrevented) {
                        return;
                    }

                    // Allow preventing the loading screen if the form has a specific class, e.g. 'no-loading'
                    if (form.classList.contains('no-loading')) {
                        return;
                    }

                    // Check if it's a SAME PAGE GET request (Search/Filter context)
                    const isGet = form.method.toLowerCase() === 'get';
                    const action = form.action || window.location.href;
                    const actionPath = new URL(action, window.location.origin).pathname;
                    const currentPath = window.location.pathname;

                    if (isGet && actionPath === currentPath) {
                        showLoading('skeleton');
                    } else {
                        showLoading('global');
                    }
                });
            });

            // 2. Global Navigation Links (exclude target="_blank", #links, and specific classes)
            document.addEventListener('click', function (e) {
                const link = e.target.closest('a');

                if (link) {
                    const href = link.getAttribute('href');
                    const target = link.getAttribute('target');

                    // Skip if:
                    // - It's a hash link (#)
                    // - It opens in a new tab
                    // - It has 'no-loading' class
                    // - It's a javascript: protocol
                    // - It is strictly a download link (optional check, dependent on attr)
                    if (
                        !href ||
                        href.startsWith('#') ||
                        href.startsWith('javascript:') ||
                        target === '_blank' ||
                        link.classList.contains('no-loading') ||
                        link.dataset.toggle === 'modal' || // Bootstrap modals
                        link.dataset.bsToggle === 'modal'  // Bootstrap 5 modals
                    ) {
                        return;
                    }

                    // Special handling for your existing .single-click-link (combine logic)
                    if (link.classList.contains('single-click-link')) {
                        // The existing logic already adds a smaller spinner, but we can also show the generic overlay 
                        // if we want a full screen block. 
                        // For now, let's respect the user's request for "Loading, please wait..."
                        showLoading();
                        return;
                    }


                    // Check if it's an internal link
                    if (href.startsWith(window.location.origin) || href.startsWith('/')) {
                        // FIX: Prevent reloading and showing overlay if clicking present link
                        // Normalize URLs by removing trailing slashes and hashes for comparison
                        const currentUrl = window.location.href.split('#')[0].replace(/\/$/, "");
                        const targetUrl = link.href.split('#')[0].replace(/\/$/, "");

                        if (targetUrl === currentUrl) {
                            e.preventDefault();
                            return;
                        }

                        // Determine Loading Type
                        // Logic: If path matches but params change, or it's just a query update -> Skeleton
                        // For now, let's use a simple path check.
                        const currentPath = window.location.pathname;
                        const targetPath = new URL(link.href).pathname;

                        if (currentPath === targetPath) {
                            showLoading('skeleton');
                        } else {
                            showLoading('global');
                        }
                    }
                }
            });

            // Handle Form Get Requests (Filter/Search)
            forms.forEach(form => {
                if (form.method.toLowerCase() === 'get') {
                    const actionUrl = new URL(form.action || window.location.href, window.location.origin);
                    if (actionUrl.pathname === window.location.pathname) {
                        // It's a filter/search on the same page
                        form.addEventListener('submit', function() {
                             // This listener runs BEFORE the generic one below, so we can override?
                             // No, duplicate execution. Better to handle logic inside the main submit handler.
                             // Actually, let's just let the main handler call showLoading() 
                             // and we modify showLoading logic or detecting logic there.
                        });
                    }
                }
            });
            
            // Fallback: Hide overlay if page acts restored (bfcache)
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    globalOverlay.style.display = 'none';
                    skeletonOverlay.style.display = 'none';
                }
            });
        });

        // GLOBAL: Prevent Double Clicks & Lock Navigation on Reports
        document.addEventListener('click', function (e) {
            const clickedLink = e.target.closest('.single-click-link');
            if (clickedLink) {
                if (clickedLink.dataset.processing === "true") {
                    e.preventDefault();
                    e.stopPropagation();
                    return;
                }

                // 1. Lock ALL navigation links immediately
                const allLinks = document.querySelectorAll('.single-click-link');
                allLinks.forEach(link => {
                    link.dataset.processing = "true";
                    link.classList.add('disabled', 'opacity-50');
                    link.style.pointerEvents = 'none';
                });

                // 2. Highlight the active one with a spinner
                clickedLink.classList.remove('opacity-50');
                const originalText = clickedLink.innerText;
                clickedLink.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> ${originalText}`;
            }
        });

        // GLOBAL: Prevent Back Button on Mobile (App-like behavior)
        if (window.innerWidth < 992) {
            // Robust Trap: Push state multiple times to create a 'buffer' against rapid clicking
            // This ensures that even if the user clicks back quickly, they are just traversing our dummy states
            history.pushState(null, null, location.href);
            history.pushState(null, null, location.href);
            history.pushState(null, null, location.href);

            window.addEventListener('popstate', function (event) {
                // When back is detected, immediately push state again to maintain the trap
                // 'forward' is generally ignored by browsers if there is no forward history, 
                // preventing the 'forward' button from breaking this logic is less of a concern than 'back'.
                history.pushState(null, null, location.href);
            });
        }
    </script>
</body>

</html>