<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $pageTitle ?? 'POS System' }}</title>
    
    {{-- CSS Libraries --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Vite Assets (Loads your Vue Component) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-light">
    <div id="app">
        <admin-layout
            user-name="{{ Auth::user()->name }}"
            user-role="{{ Auth::user()->role }}"
            page-title="{{ $pageTitle ?? 'Dashboard' }}"
            csrf-token="{{ csrf_token() }}"
            :out-of-stock="{{ $outOfStockCount ?? 0 }}"
            :low-stock="{{ $lowStockCount ?? 0 }}"
        >
            @yield('content')
            
        </admin-layout>
    </div>

    {{-- 1. ADD THIS: Bootstrap JS Bundle (Required for Modals) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>