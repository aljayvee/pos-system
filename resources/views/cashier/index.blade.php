@extends('cashier.layout')
<!-- Ensure CSRF Token for Fetch -->
<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')
    {{-- Libraries --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* === MODERN POS THEME (Mobile First Fixes) === */
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --bg-app: #f3f4f6;
            --text-main: #111827;
            --text-sub: #6b7280;
            --card-bg: #ffffff;
            --border-color: #e5e7eb;
        }

        /* DARK MODE OVERRIDES */
        html.dark {
            --primary: #6366f1;
            /* Lighter Indigo */
            --primary-dark: #818cf8;
            --bg-app: #0f172a;
            /* Slate 900 */
            --text-main: #f8fafc;
            /* Slate 50 */
            --text-sub: #94a3b8;
            /* Slate 400 */
            --card-bg: #1e293b;
            /* Slate 800 */
            --border-color: #334155;
            /* Slate 700 */
        }

        /* DARK MODE UTILITY OVERRIDES */
        html.dark body {
            color: var(--text-main);
        }

        html.dark .bg-light {
            background-color: var(--card-bg) !important;
            color: var(--text-main);
        }

        html.dark .bg-white {
            background-color: var(--card-bg) !important;
            color: var(--text-main);
        }

        html.dark .text-dark {
            color: var(--text-main) !important;
        }

        html.dark .text-muted {
            color: var(--text-sub) !important;
        }

        html.dark .btn-light {
            background-color: var(--card-bg);
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        html.dark .form-control {
            background-color: var(--bg-app);
            color: var(--text-main);
            border-color: var(--border-color);
        }

        html.dark .form-control::placeholder {
            color: var(--text-sub);
            opacity: 1;
        }

        html.dark .shadow-sm {
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.5) !important;
        }

        /* DARK MODE COMPONENT OVERRIDES */
        html.dark .card {
            background-color: var(--card-bg);
            color: var(--text-main);
            border-color: var(--border-color);
        }

        html.dark .offcanvas {
            background-color: var(--card-bg);
            color: var(--text-main);
        }

        html.dark .modal-content {
            background-color: var(--card-bg);
            color: var(--text-main);
            border-color: var(--border-color);
        }

        html.dark .list-group-item {
            background-color: var(--card-bg);
            color: var(--text-main);
            border-color: var(--border-color);
        }

        /* --- FIX: FULL SCREEN BACKDROP FOR MOBILE --- */
        .modal-backdrop,
        .offcanvas-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100% !important;
            height: 100% !important;
            z-index: 1040 !important;
            /* Ensure it covers sticky footer (1020) */
        }

        /* Modal Backdrop should be higher if modal is open */
        .modal-backdrop {
            z-index: 1055 !important;
        }

        /* Ensure Offcanvas is above its backdrop */
        .offcanvas {
            z-index: 1045 !important;
        }

        /* Ensure Modal is above its backdrop */
        .modal {
            z-index: 1060 !important;
        }

        html.dark .list-group-item-action:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text-main);
        }

        html.dark .dropdown-menu {
            background-color: var(--card-bg);
            border-color: var(--border-color);
        }

        html.dark .dropdown-item {
            color: var(--text-main);
        }

        html.dark .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        html.dark .nav-link {
            color: var(--text-main);
        }


        html.dark body {
            color: var(--text-main);
        }

        /* 1. CRITICAL FIX: PREVENT HORIZONTAL SCROLL (White Lines) */
        html,
        body {
            height: 100%;
            width: 100%;
            overflow: hidden;
            /* Disable global scroll to enforce flex layout */
            position: relative;
            background-color: var(--bg-app);
            font-family: 'Inter', system-ui, sans-serif;
        }

        /* SEARCH BAR */
        .search-wrapper {
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            padding: 6px;
            transition: box-shadow 0.2s;
        }

        .search-wrapper:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .search-input {
            border: none;
            background: transparent;
            font-size: 0.95rem;
            padding: 8px 12px;
            width: 100%;
            outline: none;
        }

        /* CATEGORY PILLS */
        .category-scroll {
            -ms-overflow-style: none;
            scrollbar-width: none;
            padding: 4px 2px 12px 2px;
            white-space: nowrap;
            /* Prevent wrapping */
        }

        .category-scroll::-webkit-scrollbar {
            display: none;
        }

        .category-btn {
            border: 1px solid var(--border-color);
            background: var(--card-bg);
            color: var(--text-sub);
            font-weight: 600;
            font-size: 0.85rem;
            padding: 8px 16px;
            border-radius: 50px;
            transition: all 0.2s;
        }

        .category-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }

        .custom-scrollbar-hidden {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        .custom-scrollbar-hidden::-webkit-scrollbar {
            display: none;
        }

        /* PRODUCT CARD */
        .product-card-wrapper {
            padding: 4px;
        }

        /* Tighter padding for mobile */

        .product-item {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            /* Slightly smaller radius for mobile */
            cursor: pointer;
            position: relative;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.1s;
        }

        .product-item:active {
            transform: scale(0.96);
        }

        .product-img-box {
            height: 100px;
            /* Compact height */
            background: var(--bg-app);
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #f1f5f9;
            color: #cbd5e1;
        }

        .product-content {
            padding: 10px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        /* STOCK BADGE */
        .stock-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            font-size: 0.65rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #fecaca;
            color: #dc2626;
            z-index: 10;
        }

        /* DESKTOP LAYOUT */
        @media (min-width: 992px) {
            .mobile-footer {
                display: none !important;
            }

            .desktop-cart-col {
                display: block;
                height: calc(100vh - 40px);
                position: sticky;
                top: 20px;
            }

            .product-grid-container {
                height: 78vh;
            }

            .container-fluid {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }

        /* MOBILE LAYOUT (< 991px) */
        @media (max-widt : 991px) {
            .desktop-cart-col {
                display: none;
            }

            .product-grid-container {
                padding-bottom: 160px;
            }

            .mobile-auto-height {
                height: auto !important;
                overflow: visible !important;
            }

            /* Reset Container Padding for edge-to-edge feel on small screens */
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .sticky-tools {
                position: sticky;
                top: 0;
                z-index: 99;
                background: var(--bg-app);
                padding-top: 10px;
                padding-bottom: 5px;
            }
        }
    </style>

    <div id="connection-status" class="status-online"
        style="height:3px; position:fixed; top:0; width:100%; z-index:9999; pointer-events: none;">
    </div>
    <div class="container-fluid px-0 h-100 d-flex flex-column">

        {{-- MOBILE HEADER & SEARCH (Sticky) --}}
        <div class="d-lg-none sticky-top border-bottom shadow-sm" style="z-index: 1040; background-color: var(--card-bg);">
            <div class="px-3 py-2 d-flex align-items-center justify-content-between gap-2">

                {{-- HAMBURGER BUTTON --}}
                <button class="btn btn-light border-0 p-1 me-1" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#mobileNavDrawer">
                    <i class="fas fa-bars fa-lg text-dark"></i>
                </button>


                <h5 class="m-0 fw-bold text-dark text-nowrap d-none d-sm-block"><i
                        class="fas fa-cash-register text-primary me-2"></i>POS</h5>

                <div class="position-relative flex-grow-1">
                    <i class="fas fa-search text-muted position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                    <input type="text" id="product-search-mobile" class="form-control bg-light border-0 ps-5 rounded-pill"
                        placeholder="Search item...">
                </div>
                <button class="btn btn-light rounded-circle shadow-sm flex-shrink-0" onclick="openCameraModal()"
                    style="width: 40px; height: 40px;">
                    <i class="fas fa-barcode text-dark"></i>
                </button>
            </div>
            {{-- Category Pills --}}
            <div class="px-2 pb-2 overflow-auto custom-scrollbar-hidden d-flex gap-2">
                <button class="category-btn active rounded-pill border px-3 py-1 small fw-bold text-nowrap"
                    onclick="filterCategory('all', this)">All</button>
                @foreach($categories as $cat)
                    <button class="category-btn rounded-pill border px-3 py-1 small fw-bold text-nowrap"
                        onclick="filterCategory('{{ strtolower($cat->name) }}', this)">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="row g-0 flex-grow-1 overflow-hidden" style="min-height: 0;">

            {{-- LEFT: PRODUCT AREA --}}
            <div class="col-lg-8 col-12 h-100 d-flex flex-column" style="min-height: 0;">

                {{-- DESKTOP TOOLBAR (Hidden Mobile) --}}
                <div class="d-none d-lg-block p-3 border-bottom bg-transparent">
                    {{-- STATS WIDGET (Neumorphic) --}}
                    @if(config('safety_flag_features.cashier_stats_widgets'))
                        {{-- STATS WIDGET (3D Isometric + Animated) --}}
                        <div class="isometric-stats-grid mb-4 isometric-container perspective-1000">
                            <div
                                class="isometric-card neumorph-card p-3 flex-fill d-flex align-items-center gap-3 position-relative overflow-hidden">
                                <div class="bg-primary bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center shadow-lg"
                                    style="width: 48px; height: 48px; z-index: 2;">
                                    <i class="fas fa-coins fa-lg"></i>
                                </div>
                                <div style="z-index: 2;">
                                    <small class="text-muted fw-bold text-uppercase"
                                        style="font-size: 0.65rem; letter-spacing: 1px;">Session Sales</small>
                                    <h4 class="fw-bold m-0 text-dark">₱{{ number_format($sessionSales, 2) }}</h4>
                                </div>
                                {{-- Animated Graph SVG --}}
                                <svg class="position-absolute bottom-0 end-0 mb-n1 me-n1" width="100" height="50"
                                    viewBox="0 0 100 50" fill="none" class="opacity-25">
                                    <path d="M0 50 L20 30 L40 40 L60 20 L80 35 L100 10 V50 H0 Z" fill="url(#grad1)"
                                        opacity="0.2"></path>
                                    <path d="M0 50 L20 30 L40 40 L60 20 L80 35 L100 10" stroke="var(--primary-color)"
                                        stroke-width="2" fill="none" class="float-anim"></path>
                                    <defs>
                                        <linearGradient id="grad1" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" style="stop-color:var(--primary-color);stop-opacity:1" />
                                            <stop offset="100%" style="stop-color:white;stop-opacity:0" />
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>

                            <div
                                class="isometric-card neumorph-card p-3 flex-fill d-flex align-items-center gap-3 position-relative overflow-hidden">
                                <div class="bg-info bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center shadow-lg"
                                    style="width: 48px; height: 48px; z-index: 2;">
                                    <i class="fas fa-receipt fa-lg"></i>
                                </div>
                                <div style="z-index: 2;">
                                    <small class="text-muted fw-bold text-uppercase"
                                        style="font-size: 0.65rem; letter-spacing: 1px;">Total Orders</small>
                                    <h4 class="fw-bold m-0 text-dark">{{ $totalOrders }}</h4>
                                </div>
                                {{-- Animated Graph SVG --}}
                                <svg class="position-absolute bottom-0 end-0 mb-n1 me-n1" width="100" height="50"
                                    viewBox="0 0 100 50" fill="none" class="opacity-25">
                                    <path d="M0 45 L25 35 L50 40 L75 25 L100 15 V50 H0 Z" fill="url(#grad2)" opacity="0.2">
                                    </path>
                                    <path d="M0 45 L25 35 L50 40 L75 25 L100 15" stroke="#0ea5e9" stroke-width="2" fill="none"
                                        class="float-anim" style="animation-delay: 1s;"></path>
                                    <defs>
                                        <linearGradient id="grad2" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" style="stop-color:#0ea5e9;stop-opacity:1" />
                                            <stop offset="100%" style="stop-color:white;stop-opacity:0" />
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>

                            <div
                                class="isometric-card neumorph-card p-3 flex-fill d-flex align-items-center gap-3 position-relative overflow-hidden">
                                <div class="bg-warning bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center shadow-lg"
                                    style="width: 48px; height: 48px; z-index: 2;">
                                    <i class="fas fa-chart-line fa-lg"></i>
                                </div>
                                <div style="z-index: 2;">
                                    <small class="text-muted fw-bold text-uppercase"
                                        style="font-size: 0.65rem; letter-spacing: 1px;">Performance</small>
                                    <h4 class="fw-bold m-0 text-dark">{{ $performance }}</h4>
                                </div>
                                {{-- Animated Graph SVG --}}
                                <svg class="position-absolute bottom-0 end-0 mb-n1 me-n1" width="100" height="50"
                                    viewBox="0 0 100 50" fill="none" class="opacity-25">
                                    <path d="M0 40 Q50 10 100 30 V50 H0 Z" fill="url(#grad3)" opacity="0.2"></path>
                                    <path d="M0 40 Q50 10 100 30" stroke="#f59e0b" stroke-width="2" fill="none"
                                        class="float-anim" style="animation-delay: 2s;"></path>
                                    <defs>
                                        <linearGradient id="grad3" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:1" />
                                            <stop offset="100%" style="stop-color:white;stop-opacity:0" />
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>
                        </div>
                    @endif
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <div class="search-wrapper flex-grow-1" style="min-width: 200px;">
                            <i class="fas fa-search text-muted ms-2"></i>
                            <input type="text" id="product-search-desktop" class="search-input"
                                placeholder="Search products (SKU or Name)">
                            <button class="btn btn-sm text-primary" onclick="openCameraModal()"><i
                                    class="fas fa-barcode fa-lg"></i></button>
                        </div>

                        {{-- VIEW TOGGLE BUTTON --}}
                        <button class="btn btn-white border shadow-sm rounded-3 px-3 text-secondary" id="view-toggle-btn"
                            onclick="toggleViewMode()" title="Toggle View">
                            <i class="fas fa-list"></i>
                        </button>

                        <button class="btn btn-white border shadow-sm rounded-3 px-3 text-secondary"
                            onclick="requestAdminAuth(openDebtorList)" title="Debtors">
                            <i class="fas fa-book text-danger"></i>
                        </button>
                        <button class="btn btn-white border shadow-sm rounded-3 px-3 text-secondary"
                            onclick="requestAdminAuth(openReturnModal)" title="Returns">
                            <i class="fas fa-undo-alt text-warning"></i>
                        </button>
                        {{-- X-READING (Manager Only) --}}
                        @if(config('safety_flag_features.bir_tax_compliance'))
                            <button class="btn btn-white border shadow-sm rounded-3 px-3 text-secondary"
                                onclick="requestAdminAuth(() => window.open('/cashier/reading/x', '_blank', 'width=400,height=600'))"
                                title="X-Reading Report">
                                <i class="fas fa-file-invoice-dollar text-primary"></i>
                            </button>
                        @endif


                    </div>
                    <div class="category-scroll d-flex gap-2 overflow-auto mt-2">
                        <button class="category-btn active" onclick="filterCategory('all', this)">All Items</button>
                        @foreach($categories as $cat)
                            <button class="category-btn" onclick="filterCategory('{{ strtolower($cat->name) }}', this)">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Product Grid --}}
                <div class="flex-grow-1 overflow-auto p-2 p-lg-3 product-grid-container" id="product-list-container">

                    {{-- List View Headers (Hidden by default) --}}
                    <div class="list-headers text-muted small fw-bold border-bottom pb-2 mb-2 px-3" style="display: none;">
                        <div style="width: 140px; margin-right: 15px;">SKU</div>
                        <div class="flex-grow-1">Product Name</div>
                        <div style="width: 120px; margin: 0 15px; text-align: right;">Price</div>
                        <div style="width: 80px; margin: 0 15px 0 0; text-align: center;">Stocks</div>
                        <div style="width: 100px; text-align: center;">Status</div>
                    </div>

                    <div class="row g-2" id="product-list">
                        @foreach($products as $product)
                            <div class="col-6 col-md-4 col-xl-3 product-card-wrapper"
                                data-name="{{ strtolower($product->name) }}" data-sku="{{ $product->sku }}"
                                data-category="{{ strtolower($product->category->name ?? '') }}">

                                <div class="card neumorph-card border-0 h-100 product-item position-relative overflow-hidden"
                                    onclick='addToCart("{{ $product->id }}")'>
                                    <div class="card-img-top d-flex align-items-center justify-content-center bg-transparent"
                                        style="height: 120px;">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                                class="w-100 h-100 object-fit-cover rounded-top-4" loading="lazy" decoding="async">
                                        @else
                                            <i class="fas fa-box fa-3x text-muted opacity-25"></i>
                                        @endif
                                    </div>

                                    <div class="card-body p-2 d-flex flex-column position-relative">
                                        {{-- Stock Badge --}}
                                        <span class="badge rounded-pill shadow-sm stock-badge"
                                            id="product-stock-{{ $product->id }}"
                                            style="background-color: rgba(255, 255, 255, 0.9); color: #dc3545; border: 1px solid #f8d7da; font-size: 0.7rem; display: {{ $product->current_stock <= ($product->reorder_point ?? 10) ? 'inline-block' : 'none' }};">
                                            {{ $product->current_stock }} Left
                                        </span>

                                        {{-- Promo Badge --}}
                                        @if($product->pricingTiers->count() > 0)
                                            <span
                                                class="badge rounded-pill shadow-sm bg-warning text-dark border border-warning promo-badge"
                                                style="font-size: 0.7rem; z-index:10;">
                                                Promo
                                            </span>
                                        @endif

                                        {{-- SKU for List View --}}
                                        <div class="product-sku text-muted small mb-0">
                                            <span class="fw-bold d-none">SKU:</span> {{ $product->sku }}
                                        </div>

                                        <h6 class="card-title text-dark small fw-bold mb-1 text-truncate">{{ $product->name }}
                                        </h6>
                                        <div class="mt-auto d-flex justify-content-between align-items-end price-wrapper">
                                            <div class="text-primary fw-bold">₱{{ number_format($product->price, 2) }}</div>
                                            <small class="text-muted"
                                                style="font-size: 0.7rem;">/{{ $product->unit ?? 'pc' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-lg-none w-100" style="height: 200px;"></div>

                    {{-- Empty State --}}
                    <div id="no-results" class="d-none text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3 opacity-50"></i>
                        <h5 class="text-muted">No products found</h5>
                    </div>
                </div>
            </div>

            {{-- RIGHT: CART (Desktop Only) --}}
            <div class="col-lg-4 d-none d-lg-flex flex-column h-100 p-0 desktop-cart-col col-fixed-right">
                <div class="glass-panel d-flex flex-column h-100 m-3 rounded-4 border-0 shadow-lg overflow-hidden">
                    <div class="p-3 border-bottom bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold m-0"><i class="fas fa-shopping-cart me-2"></i>Current Order</h5>
                        <button class="btn btn-sm btn-outline-danger shadow-sm py-1 px-2" onclick="clearCart()"
                            title="Clear Cart">
                            <i class="fas fa-trash-alt me-1"></i> Clear
                        </button>
                    </div>
                    <div class="flex-grow-1 overflow-auto p-3" id="cart-items-desktop">
                        {{-- Injected via JS --}}
                    </div>
                    <div class="p-3 border-top bg-light">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-bold">₱<span class="subtotal-display">0.00</span></span>
                        </div>
                        <div class="d-none justify-content-between mb-2 tax-row">
                            <span class="text-muted small">Vatable Sales</span>
                            <span class="fw-bold small">₱<span id="vatable-sales-display">0.00</span></span>
                        </div>
                        <div class="d-none justify-content-between mb-2 tax-row">
                            <span class="text-muted small">VAT Amount (12%)</span>
                            <span class="fw-bold small">₱<span class="tax-display">0.00</span></span>
                        </div>
                        <div class="d-none justify-content-between mb-2 tax-row">
                            <span class="text-muted small">VAT Exempt Sales</span>
                            <span class="fw-bold small">₱<span id="vat-exempt-display">0.00</span></span>
                        </div>

                        {{-- Discount Row --}}
                        <div id="discount-row" class="d-flex justify-content-between mb-2 text-danger"
                            style="display:none;">
                            <span class="small fw-bold" id="discount-label">Discount</span>
                            <span class="small fw-bold">-₱<span id="discount-amount-display">0.00</span></span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3 border-top pt-2">
                            <span class="h5 fw-bold m-0">Total</span>
                            <span class="h4 fw-bold text-primary m-0">₱<span class="total-amount-display">0.00</span></span>
                        </div>

                        {{-- Discount Buttons --}}
                        @if($birEnabled)
                            <div class="mb-3">
                                <button id="btn-add-discount" class="btn btn-sm btn-outline-secondary w-100 border-dashed"
                                    onclick="openDiscountModal()">
                                    <i class="fas fa-percent me-2"></i> Add Discount (Senior/PWD)
                                </button>
                                <button id="btn-remove-discount" class="btn btn-sm btn-outline-danger w-100 d-none"
                                    onclick="removeDiscount()">
                                    <i class="fas fa-times me-2"></i> Remove Discount
                                </button>
                            </div>
                        @endif

                        <div class="d-grid gap-2">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-user"></i></span>
                                <select id="customer-id" class="form-select">
                                    <option value="walk-in" data-balance="0" data-points="0">Walk-in Customer</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" data-balance="{{ $c->balance }}"
                                            data-points="{{ $c->points }}">{{ $c->name }}</option>
                                    @endforeach
                                    <option value="new" class="fw-bold text-primary">+ New Customer Utang</option>
                                </select>
                            </div>
                            <button class="btn btn-primary btn-lg fw-bold" onclick="openPaymentModal()">PAY NOW</button>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MOBILE FULL-WIDTH STICKY FOOTER --}}
        <div class="d-lg-none fixed-bottom bg-white border-top shadow-lg p-3 mobile-footer-panel"
            style="z-index: 1020; padding-bottom: env(safe-area-inset-bottom) !important;">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex flex-column">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Total Due</small>
                    <span class="fw-bold fs-3 text-dark lh-1">₱<span id="mobile-total-display">0.00</span></span>
                </div>
                <button
                    class="btn btn-primary rounded-pill fw-bold px-4 flex-grow-1 py-3 shadow-lg d-flex justify-content-between align-items-center"
                    type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileCartDrawer"
                    style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); border:none;">
                    <span class="fs-6">View Cart</span>
                    <span class="badge bg-white text-primary rounded-pill px-3 py-2 shadow-sm" id="mobile-cart-count"
                        style="font-size: 0.85rem;">0</span>
                </button>
            </div>
        </div>
    </div>

    {{-- OFFCANVAS CART --}}
    <div class="offcanvas offcanvas-bottom rounded-top-4" tabindex="-1" id="mobileCartDrawer"
        style="height: 85vh; z-index: 1050;">
        <div class="offcanvas-header border-bottom py-3">
            <h5 class="offcanvas-title fw-bold">Current Order</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0 bg-light d-flex flex-column">
            <div class="flex-grow-1 overflow-auto p-3" id="cart-items">
                {{-- Injected via JS --}}
                <div class="text-center py-5 text-muted empty-cart-msg">
                    <i class="fas fa-shopping-basket fa-3x mb-3 opacity-25"></i>
                    <p>Your cart is empty</p>
                </div>
            </div>

            {{-- Mobile Cart Footer (Summary + Pay) --}}
            <div class="p-3 bg-white border-top shadow-lg z-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span class="fw-bold">₱<span class="subtotal-display">0.00</span></span>
                </div>

                {{-- Discount Row --}}
                <div id="discount-row-mobile" class="d-flex justify-content-between mb-2 text-danger" style="display:none;">
                    <span class="small fw-bold" id="discount-label-mobile">Discount</span>
                    <span class="small fw-bold">-₱<span id="discount-amount-display-mobile">0.00</span></span>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="h5 fw-bold m-0">Total</span>
                    <span class="h3 fw-bold text-primary m-0">₱<span class="total-amount-display">0.00</span></span>
                </div>

                <div class="mb-3">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-primary"></i></span>
                        <select id="customer-id-mobile" class="form-select border-start-0 ps-0 bg-white fw-bold text-dark">
                            <option value="walk-in" data-balance="0" data-points="0">Walk-in Customer</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" data-balance="{{ $c->balance }}" data-points="{{ $c->points }}">
                                    {{ $c->name }}
                                </option>
                            @endforeach
                            <option value="new" class="fw-bold text-primary">+ New Customer Utang</option>
                        </select>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-primary btn-lg fw-bold rounded-pill shadow-sm" onclick="openPaymentModal()">
                        PROCEED TO PAY
                    </button>
                    <button class="btn btn-outline-danger shadow-sm rounded-pill fw-bold" onclick="clearCart()">
                        CLEAR CART
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('modals')
        @include('cashier.partials.modals')
    @endpush

    {{-- CART TEMPLATE --}}
    <script id="cart-template" type="text/template">
                                                                                                                                                                                                                                            @include('cashier.partials.cart-ui')
                                                                                                                                                                                                                                        </script>

    {{-- Pass PHP Config to JS via Data Attributes (Safe from Syntax Errors) --}}
    <div id="cashier-config" data-bir-enabled="{{ $birEnabled }}"
        data-paymongo-enabled="{{ \App\Models\Setting::where('key', 'enable_paymongo')->value('value') ?? 0 }}"
        data-points-value="{{ \App\Models\Setting::where('key', 'points_conversion')->value('value') ?? 1 }}"
        data-loyalty-enabled="{{ $loyaltyEnabled }}" data-user-role="{{ Auth::user()->role }}"
        data-register-logs="{{ $registerLogsEnabled }}" data-register-open="{{ $isRegisterOpen ? '1' : '0' }}"
        data-tax-type="{{ $taxType }}" style="display:none;"></div>

    <script>
        // --- VIEW    MODE           LO            GIC ---
        function toggleViewMode() {
            // TARGET PARENT CONTAINER
            const container = document.getElementById('product-list-container');
            const btn = document.getElementById('view-toggle-btn');
            const icon = btn.querySelector('i');

            if (container.classList.contains('list-view-mode')) {
                // Switch to Grid
                container.classList.remove('list-view-mode');
                icon.classList.remove('fa-th-large');
                icon.classList.add('fa-list');
                localStorage.setItem('cashier_view_mode', 'grid');
            } else {
                // Switch to List
                container.classList.add('list-view-mode');
                icon.classList.remove('fa-list');
                icon.classList.add('fa-th-large');
                localStorage.setItem('cashier_view_mode', 'list');
            }
        }

        // Apply saved preference on load
        document.addEventListener('DOMContentLoaded', () => {
            const savedMode = localStorage.getItem('cashier_view_mode');
            if (savedMode === 'list') {
                document.getElementById('product-list-container').classList.add('list-view-mode');
                const btn = document.getElementById('view-toggle-btn');
                if (btn) {
                    btn.querySelector('i').classList.remove('fa-list');
                    btn.querySelector('i').classList.add('fa-th-large');
                }
            }
        });
    </script>

    <script>
        // Inject Products Safely
        window.ALL_PRODUCTS = @json($products);
    </script>

    @vite(['resources/js/cashier.js'])




    {{-- 3. RECEIPT OVERVIEW MODAL --}}
    <div class="modal fade" id="receiptSuccessModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content glass-modal border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-body p-0 text-center">
                    {{-- Success Header --}}
                    <div class="bg-success text-white p-4">
                        <div
                            class="bg-white bg-opacity-25 rounded-circle d-inline-flex p-3 mb-2 animate__animated animate__bounceIn">
                            <i class="fas fa-check fa-2x text-white"></i>
                        </div>
                        <h5 class="fw-bold m-0">Transaction Complete!</h5>
                    </div>

                    {{-- Details --}}
                    <div class="p-4">
                        <div class="mb-3">
                            <small class="text-uppercase text-muted fw-bold x-small">Total Amount</small>
                            <h2 class="fw-bold text-dark m-0">₱<span id="receipt-modal-amount">0.00</span></h2>
                        </div>

                        <div class="row g-2 mb-4 border-top border-bottom py-3">
                            <div class="col-6 border-end">
                                <small class="text-muted d-block x-small">Customer</small>
                                <span class="fw-bold text-truncate d-block" id="receipt-modal-customer"
                                    style="max-width: 100%;">Walk-in</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block x-small">Change</small>
                                <span class="fw-bold text-success" id="receipt-modal-change">0.00</span>
                            </div>
                        </div>

                        <p class="text-muted x-small mb-4">Ref: <span id="receipt-modal-ref">---</span></p>

                        <div class="d-grid gap-2">
                            <button id="btn-receipt-print" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                                <i class="fas fa-print me-2"></i> Print Receipt
                            </button>
                            <button id="btn-receipt-new" class="btn btn-outline-secondary rounded-pill fw-bold">
                                <i class="fas fa-plus me-2"></i> New Sale
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- ==================== CASH REGISTER MODALS ==================== --}}

    {{-- 1. OPEN REGISTER MODAL (Static Backdrop) --}}
    <div class="modal fade" id="openRegisterModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-modal border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 bg-primary text-white text-center pb-4">
                    <div class="w-100">
                        <div class="bg-white rounded-circle d-inline-flex p-3 mb-2 text-primary shadow-sm"
                            style="width: 60px; height: 60px; align-items: center; justify-content: center;">
                            <i class="fas fa-cash-register fa-lg"></i>
                        </div>
                        <h5 class="modal-title fw-bold" id="openRegisterLabel">Open Register</h5>
                        <small class="opacity-75">Please enter the opening float to start.</small>
                    </div>
                </div>
                <div class="modal-body p-4 pt-2">
                    <form id="openRegisterForm" onsubmit="submitOpenRegister(event)">
                        <div class="mb-4">
                            <label for="opening_float"
                                class="form-label fw-bold text-secondary text-uppercase small">Opening Cash Amount</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0 text-muted">₱</span>
                                <input type="number" class="form-control border-start-0 bg-light fs-4 fw-bold"
                                    id="opening_float" name="opening_float" required min="0" step="0.01" placeholder="0.00"
                                    autofocus>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg rounded-pill fw-bold shadow-sm">
                            <i class="fas fa-unlock me-2"></i> Open Register
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. CLOSE REGISTER MODAL --}}
    <div class="modal fade" id="closeRegisterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 bg-danger text-white pb-3">
                    <h5 class="modal-title fw-bold"><i class="fas fa-store-slash me-2"></i>Close Register</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="closeRegisterForm" onsubmit="submitCloseRegister(event)">
                        <input type="hidden" id="close_session_id">

                        <div class="alert alert-warning border-0 d-flex align-items-center mb-4">
                            <i class="fas fa-exclamation-triangle fa-2x me-3 opacity-50"></i>
                            <div>
                                <div class="fw-bold">Blind Count Required</div>
                                <div class="small">Count the physical cash in the drawer immediately.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="closing_amount"
                                class="form-label fw-bold text-secondary text-uppercase small">Actual Cash Counted</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0 text-muted">₱</span>
                                <input type="number" class="form-control border-start-0 bg-light fs-4 fw-bold text-danger"
                                    id="closing_amount" name="closing_amount" required min="0" step="0.01"
                                    placeholder="0.00">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="closing_notes" class="form-label text-secondary small">Notes / Remarks
                                (Optional)</label>
                            <textarea class="form-control" id="closing_notes" rows="2"
                                placeholder="e.g., Short ₱5 due to loose change..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 btn-lg rounded-pill fw-bold shadow-sm">
                            <i class="fas fa-check-circle me-2"></i> Submit & Close
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection