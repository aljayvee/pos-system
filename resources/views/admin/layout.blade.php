<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $pageTitle ?? 'POS System' }}</title>
    
    {{-- CSS Libraries --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('styles')
</head>

<body class="bg-gray-100">
    <div id="app">
        {{-- The Vue Component now handles the full layout (Sidebar + Header + Main) --}}
        <admin-layout
            user-name="{{ Auth::user()->name }}"
            user-role="{{ Auth::user()->role }}"
            page-title="{{ $pageTitle ?? 'Dashboard' }}"
            csrf-token="{{ csrf_token() }}"
            :out-of-stock="{{ $outOfStockCount ?? 0 }}"
            :low-stock="{{ $lowStockCount ?? 0 }}"
        >
            {{-- This slot content goes inside the Vue component's <main> tag --}}
            @yield('content')
            
        </admin-layout>
    </div>

    @stack('scripts')
</body>
</html>