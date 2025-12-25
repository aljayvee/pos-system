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
        >
            @yield('content')
            
        </admin-layout>
        <offline-indicator></offline-indicator>
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