<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>POS System - Admin</title>
    
    {{-- CSS Libraries --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1e1e2d">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- STACK: STYLES --}}
    @stack('styles')

    <style>
    /* BASE VARIABLES */
    :root { 
        --sidebar-width: 280px; 
        --top-nav-height: 70px; 
        --primary-dark: #1e1e2d; 
        --secondary-dark: #151521; 
        --text-muted: #9899ac; 
        --text-light: #e4e6ef; 
        --active-bg: rgba(54, 153, 255, 0.1); 
        --active-text: #3699ff; 
        --danger-color: #f64e60; 
    }
    
    body { background-color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-x: hidden; }

    /* SIDEBAR CONTAINER */
    #sidebar-wrapper { 
        width: var(--sidebar-width); 
        height: 100vh; 
        position: fixed; 
        top: 0; 
        left: 0; 
        z-index: 1050; 
        background-color: var(--primary-dark); 
        color: var(--text-muted); 
        transition: transform 0.3s ease-in-out; /* Smooth transition is key */
        display: flex; 
        flex-direction: column; 
        box-shadow: 4px 0 25px rgba(0,0,0,0.1); 
    }

    /* CONTENT CONTAINER */
    #page-content-wrapper { 
        width: 100%; 
        min-height: 100vh;
        transition: margin-left 0.3s ease-in-out;
        /* Default: Desktop View (Sidebar Open) */
        margin-left: var(--sidebar-width); 
    }

    /* --- RESPONSIVE BEHAVIOR (The Missing Part) --- */
    
    /* DESKTOP (Screens >= 992px) */
    @media (min-width: 992px) {
        /* When 'desktop-closed' class is added by Vue, hide sidebar */
        #app.desktop-closed #sidebar-wrapper { 
            transform: translateX(-100%); 
        }
        /* When sidebar is closed, content takes full width */
        #app.desktop-closed #page-content-wrapper { 
            margin-left: 0; 
        }
    }

    /* MOBILE (Screens < 992px) */
    @media (max-width: 991.98px) {
        /* Default state on mobile: Content full width, sidebar hidden */
        #page-content-wrapper { 
            margin-left: 0 !important; 
        }
        #sidebar-wrapper { 
            transform: translateX(-100%); 
        }
        
        /* When 'mobile-open' class is added by Vue, show sidebar */
        #app.mobile-open #sidebar-wrapper { 
            transform: translateX(0); 
        }
        
        /* Mobile Backdrop */
        .sidebar-backdrop { 
            display: none; 
            position: fixed; inset: 0; 
            background: rgba(0,0,0,0.5); 
            z-index: 1040; 
            backdrop-filter: blur(2px); 
        }
        #app.mobile-open .sidebar-backdrop { 
            display: block; 
        }
    }

    /* COMPONENT STYLES */
    .sidebar-header { height: var(--top-nav-height); display: flex; align-items: center; padding: 0 24px; background-color: var(--secondary-dark); border-bottom: 1px solid rgba(255,255,255,0.05); }
    .sidebar-content { flex-grow: 1; overflow-y: auto; padding: 20px 0; }
    
    .list-group-item { background: transparent; border: none; color: var(--text-muted); padding: 12px 24px; font-size: 0.95rem; font-weight: 500; display: flex; align-items: center; border-left: 3px solid transparent; transition: all 0.2s ease; gap: 16px; text-decoration: none; }
    .list-group-item:hover { background-color: rgba(255,255,255,0.03); color: var(--text-light); }
    .list-group-item.active { background-color: var(--active-bg); color: var(--active-text); font-weight: 600; border-left-color: var(--active-text); }
    .list-group-item i { width: 24px; text-align: center; }
    .list-group-item.active i { color: var(--active-text); }
    
    .menu-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1.2px; color: #5d5f75; padding: 24px 24px 8px; font-weight: 700; }
    .sidebar-footer { padding: 20px 24px; background-color: var(--secondary-dark); border-top: 1px solid rgba(255,255,255,0.05); flex-shrink: 0; }
    
    .user-card { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .user-avatar { width: 42px; height: 42px; background: var(--active-text); color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; }
    .user-name { color: white; font-weight: 700; font-size: 0.95rem; }
    .user-role { color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 600; }
    
    .btn-logout { width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; background: rgba(246, 78, 96, 0.1); color: var(--danger-color); border: 1px solid transparent; padding: 10px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: all 0.2s; cursor: pointer;}
    .btn-logout:hover { background: var(--danger-color); color: white; }
    
    /* Navbar Sticky */
    .sticky-top { z-index: 1020; }
    
    /* Ensure buttons feel clickable */
    button, a { cursor: pointer; }

    /* Notification Dropdown */
    .notification-menu { width: 320px; border: 0; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); overflow: hidden; z-index: 1060; }
    
    @media (max-width: 991.98px) {
        .notification-menu { 
            position: fixed !important; 
            top: 75px !important; 
            left: 50% !important; 
            transform: translateX(-50%) !important; 
            width: 92% !important; 
            max-width: 360px;
            z-index: 1100 !important; 
        }
    }
</style>
</head>

<body>
    <div id="app">
        {{-- Pass Blade variables into Vue Props here --}}
        <sidebar-layout
            user-name="{{ Auth::user()->name }}"
            user-role="{{ Auth::user()->role }}"
            page-title="@yield('title', 'Dashboard')"
            csrf-token="{{ csrf_token() }}"
            :out-of-stock="{{ $outOfStockCount ?? 0 }}"
            :low-stock="{{ $lowStockCount ?? 0 }}"
        >
            {{-- This content goes into the <slot> --}}
            @yield('content')
            
        </sidebar-layout>
    </div>

    {{-- STACK: SCRIPTS --}}
    @stack('scripts')
</body>
</html>