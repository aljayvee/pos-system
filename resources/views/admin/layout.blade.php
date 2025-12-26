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
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        #global-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: white;
            font-family: 'Inter', sans-serif; /* Assuming Inter is used, fallback to sans-serif */
        }
        
        #global-loading-overlay .spinner-border {
            width: 3rem;
            height: 3rem;
            margin-bottom: 15px;
        }

        #global-loading-overlay h5 {
            font-weight: 500;
            letter-spacing: 0.5px;
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
<body class="bg-light">
    <div id="app">
        <admin-layout
            user-name="{{ Auth::user()->name }}"
            user-role="{{ Auth::user()->role }}"
            :user-permissions="{{ json_encode(Auth::user()->effective_permissions) }}"
            page-title="{{ $pageTitle ?? 'Dashboard' }}"
            csrf-token="{{ csrf_token() }}"
            :out-of-stock="{{ $outOfStockCount ?? 0 }}"
            :low-stock="{{ $lowStockCount ?? 0 }}"
            :enable-register-logs="{{ \App\Models\Setting::where('key', 'enable_register_logs')->value('value') ?? 0 }}"
        >
            @yield('content')
            
        </admin-layout>
        <offline-indicator></offline-indicator>
    </div>

    <!-- Global Loading Overlay -->
    <div id="global-loading-overlay">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h5 class="mt-3">Loading, please wait...</h5>
    </div>

    {{-- 1. ADD THIS: Bootstrap JS Bundle (Required for Modals) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- OPTIMIZATION: Instant Page Loads (Prefetch on hover) --}}
    <script src="//instant.page/5.2.0" type="module" integrity="sha384-jnZyxPjiipYXnSU0ygqeac2q7CVYMbh84q0uHVRRxEtvFPiQYbXWUorga2aqZJ0z" crossorigin="anonymous"></script>
    
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
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('global-loading-overlay');
            
            function showLoading() {
                overlay.style.display = 'flex';
            }

            // 1. Form Submissions
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
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

                    showLoading();
                });
            });

            // 2. Global Navigation Links (exclude target="_blank", #links, and specific classes)
            document.addEventListener('click', function(e) {
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
                        showLoading();
                    }
                }
            });

            // Fallback: Hide overlay if page acts restored (bfcache)
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    overlay.style.display = 'none';
                }
            });
        });

        // GLOBAL: Prevent Double Clicks & Lock Navigation on Reports
        document.addEventListener('click', function(e) {
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
    </script>
</body>

</html>