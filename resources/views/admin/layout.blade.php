<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
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

                0%,
                100% {
                    opacity: 0.5;
                    transform: scale(0.98);
                }

                50% {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            /* Skeleton Loading Styles */
            #skeleton-loading-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: #f1f5f9;
                /* Match body bg */
                z-index: 9999;
                /* High Z-Index to cover everything */
                display: block;
                /* Show by default for reload */
                padding: 20px;
                box-sizing: border-box;
                overflow: hidden;
            }

            .skeleton-header {
                height: 60px;
                width: 100%;
                background: #fff;
                margin-bottom: 20px;
                border-radius: 8px;
                flex-shrink: 0;
            }

            .skeleton-content {
                display: flex;
                gap: 20px;
                height: calc(100vh - 100px);
                /* Adjust for header + padding */
                overflow: hidden;
            }

            .skeleton-sidebar {
                width: 250px;
                height: 100%;
                background: #fff;
                border-radius: 8px;
                display: none;
                flex-shrink: 0;
            }

            @media (min-width: 992px) {
                .skeleton-sidebar {
                    display: block;
                }
            }

            .skeleton-main-container {
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 20px;
                height: 100%;
                overflow: hidden;
            }

            .skeleton-title {
                height: 40px;
                width: 200px;
                background: #fff;
                border-radius: 8px;
                margin-bottom: 10px;
                flex-shrink: 0;
            }

            .skeleton-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
                padding-bottom: 20px;
            }

            .skeleton-card {
                background: #fff;
                border-radius: 12px;
                height: 280px;
                /* Taller for image + text */
                width: 100%;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }

            .skeleton-card-img {
                height: 160px;
                width: 100%;
                background: #e2e8f0;
                /* Slightly darker than card bg */
            }

            .skeleton-card-body {
                padding: 16px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                gap: 10px;
                flex: 1;
            }

            .skeleton-text-line {
                height: 16px;
                border-radius: 4px;
                background: #e2e8f0;
            }

            .skeleton-text-line.lg {
                width: 80%;
                height: 20px;
                margin-bottom: 4px;
            }

            .skeleton-text-line.sm {
                width: 60%;
            }

            /* Animation */
            .skeleton-anim .skeleton-card-img,
            .skeleton-anim .skeleton-text-line,
            .skeleton-anim.skeleton-header,
            .skeleton-anim.skeleton-sidebar,
            .skeleton-anim.skeleton-title {
                position: relative;
                overflow: hidden;
            }

            .skeleton-anim .skeleton-card-img::after,
            .skeleton-anim .skeleton-text-line::after,
            .skeleton-anim.skeleton-header::after,
            .skeleton-anim.skeleton-sidebar::after,
            .skeleton-anim.skeleton-title::after {
                content: "";
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                transform: translateX(-100%);
                background-image: linear-gradient(90deg,
                        rgba(255, 255, 255, 0) 0,
                        rgba(255, 255, 255, 0.4) 20%,
                        rgba(255, 255, 255, 0.7) 60%,
                        rgba(255, 255, 255, 0));
                animation: shimmer 1.5s infinite;
            }

            @keyframes shimmer {
                100% {
                    transform: translateX(100%);
                }
            }
    </style>

</head>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Poll for login requests
    // Listen for real-time login requests
    document.addEventListener('DOMContentLoaded', () => {
        if (window.Echo) {
            console.log('Echo initialized, attempting to subscribe to: App.Models.User.{{ Auth::id() }}');

            window.Echo.private('App.Models.User.{{ Auth::id() }}')
                .subscribed(() => {
                    console.log('✅ Successfully subscribed to private channel');
                })
                .error((error) => {
                    console.error('❌ Error subscribing to private channel:', error);
                })
                .listen('.LoginRequestCreated', (e) => {
                    console.log('🔔 Login Request Event Received:', e);
                    if (e && e.details) {
                        showConsentModal(e.details);
                    } else {
                        console.error('Event received but details missing:', e);
                    }
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
            :out-of-stock="{{ \App\Models\Inventory::where('store_id', Auth::user()->store_id ?? 1)->where('stock', '<=', 0)->whereHas('product')->count() }}"
            :low-stock="{{ \App\Models\Inventory::where('store_id', Auth::user()->store_id ?? 1)->where('stock', '>', 0)->whereColumn('stock', '<=', 'reorder_point')->whereHas('product')->count() }}"
            :enable-register-logs="{{ \App\Models\Setting::where('key', 'enable_register_logs')->value('value') ?? 0 }}"
            :enable-bir-compliance="{{ config('safety_flag_features.bir_tax_compliance') ? 'true' : 'false' }}"
            :user-id="{{ Auth::id() }}" system-mode="{{ config('app.mode', 'single') }}">
            @yield('content')

        </admin-layout>
        <offline-indicator></offline-indicator>
    </div>

    @stack('modals')

    <!-- Global Loading Overlay Removed -->

    <!-- Skeleton Loading Overlay -->
    <div id="skeleton-loading-overlay">
        <div class="skeleton-header skeleton-anim"></div>
        <div class="skeleton-content">
            <div class="skeleton-sidebar skeleton-anim"></div>
            <div class="skeleton-main-container">
                <div class="skeleton-title skeleton-anim"></div>
                <div class="skeleton-grid">
                    <!-- Generate 8 cards for a full grid -->
                    <div class="skeleton-card skeleton-anim">
                        <div class="skeleton-card-img"></div>
                        <div class="skeleton-card-body">
                            <div class="skeleton-text-line lg"></div>
                            <div class="skeleton-text-line sm"></div>
                        </div>
                    </div>
                    <div class="skeleton-card skeleton-anim">
                        <div class="skeleton-card-img"></div>
                        <div class="skeleton-card-body">
                            <div class="skeleton-text-line lg"></div>
                            <div class="skeleton-text-line sm"></div>
                        </div>
                    </div>
                    <div class="skeleton-card skeleton-anim">
                        <div class="skeleton-card-img"></div>
                        <div class="skeleton-card-body">
                            <div class="skeleton-text-line lg"></div>
                            <div class="skeleton-text-line sm"></div>
                        </div>
                    </div>
                    <div class="skeleton-card skeleton-anim">
                        <div class="skeleton-card-img"></div>
                        <div class="skeleton-card-body">
                            <div class="skeleton-text-line lg"></div>
                            <div class="skeleton-text-line sm"></div>
                        </div>
                    </div>
                    <div class="skeleton-card skeleton-anim">
                        <div class="skeleton-card-img"></div>
                        <div class="skeleton-card-body">
                            <div class="skeleton-text-line lg"></div>
                            <div class="skeleton-text-line sm"></div>
                        </div>
                    </div>
                    <div class="skeleton-card skeleton-anim">
                        <div class="skeleton-card-img"></div>
                        <div class="skeleton-card-body">
                            <div class="skeleton-text-line lg"></div>
                            <div class="skeleton-text-line sm"></div>
                        </div>
                    </div>
                    <div class="skeleton-card skeleton-anim">
                        <div class="skeleton-card-img"></div>
                        <div class="skeleton-card-body">
                            <div class="skeleton-text-line lg"></div>
                            <div class="skeleton-text-line sm"></div>
                        </div>
                    </div>
                    <div class="skeleton-card skeleton-anim">
                        <div class="skeleton-card-img"></div>
                        <div class="skeleton-card-body">
                            <div class="skeleton-text-line lg"></div>
                            <div class="skeleton-text-line sm"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 1. ADD THIS: Bootstrap JS Bundle (Required for Modals) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



    @stack('scripts')

    {{-- 2. ADD THIS: Flash Message Bridge to Vue --}}
    <script>
        window.laravel_flash = {!! json_encode([
    'success' => session('success'),
    'error' => session('error'),
    'warning' => session('warning'),
    'info' => session('info')
]) !!};

        // Skeleton Loader Logic
        document.addEventListener('DOMContentLoaded', function () {
            const skeletonOverlay = document.getElementById('skeleton-loading-overlay');

            function showSkeletonLoading() {
                if (skeletonOverlay) {
                    skeletonOverlay.style.setProperty('display', 'block', 'important');
                    skeletonOverlay.style.zIndex = '2147483647';
                }
            }

            // Expose hide function globally for Vue or others
            window.hideSkeletonLoading = function () {
                if (skeletonOverlay) {
                    skeletonOverlay.style.display = 'none';
                }
            };

            // Hide on initial load
            window.addEventListener('load', function () {
                // Small delay to ensure smooth transition
                setTimeout(window.hideSkeletonLoading, 300);
            });

            // 1. Form Submissions
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function (e) {
                    if (!form.checkValidity() || e.defaultPrevented) return;
                    showSkeletonLoading();
                });
            });

            // 2. Navigation
            document.addEventListener('click', function (e) {
                const link = e.target.closest('a');
                if (link) {
                    const href = link.getAttribute('href');
                    const target = link.getAttribute('target');

                    if (
                        !href || href.startsWith('#') || href.startsWith('javascript:') || target === '_blank' ||
                        link.classList.contains('no-loading') || link.dataset.toggle === 'modal' || link.dataset.bsToggle === 'modal'
                    ) return;

                    if (href.startsWith(window.location.origin) || href.startsWith('/')) {
                        const currentUrl = window.location.href.split('#')[0].replace(/\/$/, "");
                        const targetUrl = link.href.split('#')[0].replace(/\/$/, "");
                        if (targetUrl === currentUrl) {
                            e.preventDefault(); return;
                        }
                        showSkeletonLoading();
                    }
                }
            });

            // Fallback: Hide overlay if page acts restored (bfcache)
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    if (event.persisted) {
                        if (skeletonOverlay) skeletonOverlay.style.display = 'none';
                    }
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