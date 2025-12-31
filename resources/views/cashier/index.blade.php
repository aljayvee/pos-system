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
            background: white;
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
            background: white;
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
            background: #f8fafc;
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
        @media (max-width: 991px) {
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

    <div id="connection-status" class="status-online" style="height:3px; position:fixed; top:0; width:100%; z-index:9999;">
    </div>
    <div class="container-fluid px-0 h-100 d-flex flex-column">

        {{-- MOBILE HEADER & SEARCH (Sticky) --}}
        <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm" style="z-index: 1020;">
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
                <div class="d-none d-lg-block p-3 border-bottom bg-white">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="search-wrapper flex-grow-1">
                            <i class="fas fa-search text-muted ms-2"></i>
                            <input type="text" id="product-search-desktop" class="search-input"
                                placeholder="Search products (SKU or Name)">
                            <button class="btn btn-sm text-primary" onclick="openCameraModal()"><i
                                    class="fas fa-barcode fa-lg"></i></button>
                        </div>

                        <button class="btn btn-white border shadow-sm rounded-3 px-3 text-secondary"
                            onclick="requestAdminAuth(openDebtorList)" title="Debtors">
                            <i class="fas fa-book text-danger"></i>
                        </button>
                        <button class="btn btn-white border shadow-sm rounded-3 px-3 text-secondary"
                            onclick="requestAdminAuth(openReturnModal)" title="Returns">
                            <i class="fas fa-undo-alt text-warning"></i>
                        </button>
                        <button class="btn btn-danger border shadow-sm rounded-3 px-3 fw-bold"
                            id="btn-close-register-desktop" onclick="showCloseRegisterModal()" title="Close Register">
                            <i class="fas fa-store-slash me-2"></i>Close Register
                        </button>
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
                    <div class="row g-2" id="product-list">
                        @foreach($products as $product)
                            <div class="col-6 col-md-4 col-xl-3 product-card-wrapper"
                                data-name="{{ strtolower($product->name) }}" data-sku="{{ $product->sku }}"
                                data-category="{{ strtolower($product->category->name ?? '') }}">

                                <div class="card border-0 h-100 shadow-sm product-item position-relative overflow-hidden"
                                    onclick='addToCart(@json($product))'>
                                    {{-- Stock Badge --}}
                                    <span class="badge position-absolute top-0 end-0 m-2 rounded-pill shadow-sm"
                                        id="product-stock-{{ $product->id }}"
                                        style="background-color: rgba(255, 255, 255, 0.9); color: #dc3545; border: 1px solid #f8d7da; font-size: 0.7rem; display: {{ $product->current_stock <= ($product->reorder_point ?? 10) ? 'inline-block' : 'none' }};">
                                        {{ $product->current_stock }} Left
                                    </span>

                                    {{-- Promo Badge --}}
                                    @if($product->pricingTiers->count() > 0)
                                        <span
                                            class="badge position-absolute top-0 start-0 m-2 rounded-pill shadow-sm bg-warning text-dark border border-warning"
                                            style="font-size: 0.7rem; z-index:10;">
                                            Promo
                                        </span>
                                    @endif

                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                        style="height: 120px;">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                                class="w-100 h-100 object-fit-cover" loading="lazy" decoding="async">
                                        @else
                                            <i class="fas fa-box fa-3x text-muted opacity-25"></i>
                                        @endif
                                    </div>

                                    <div class="card-body p-2 d-flex flex-column">
                                        <h6 class="card-title text-dark small fw-bold mb-1 text-truncate">{{ $product->name }}
                                        </h6>
                                        <div class="mt-auto d-flex justify-content-between align-items-end">
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
                </div>
            </div>

            {{-- RIGHT: CART (Desktop Only) --}}
            <div class="col-lg-4 desktop-cart-col border-start bg-white d-none d-lg-flex flex-column h-100 col-fixed-right">
                <div class="p-3 border-bottom bg-light">
                    <h5 class="fw-bold m-0"><i class="fas fa-shopping-cart me-2"></i>Current Order</h5>
                </div>
                <div class="flex-grow-1 overflow-auto p-3" id="cart-items-desktop">
                    {{-- Injected via JS --}}
                </div>
                <div class="p-3 border-top bg-light">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-bold">₱<span class="subtotal-display">0.00</span></span>
                    </div>
                    {{-- Tax Rows Hidden by Default --}}
                    <div class="d-flex justify-content-between mb-2 tax-row" style="display:none;">
                        <span class="text-muted small">VAT (12%)</span>
                        <span class="fw-bold small">₱<span class="tax-display">0.00</span></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="h5 fw-bold m-0">Total</span>
                        <span class="h4 fw-bold text-primary m-0">₱<span class="total-amount-display">0.00</span></span>
                    </div>

                    <div class="d-grid gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-user"></i></span>
                            <select id="customer-id" class="form-select">
                                <option value="walk-in" data-balance="0" data-points="0">Walk-in Customer</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" data-balance="{{ $c->balance }}"
                                        data-points="{{ $c->points }}">{{ $c->name }}</option>
                                @endforeach
                                <option value="new">+ New Customer</option>
                            </select>
                        </div>
                        <button class="btn btn-primary btn-lg fw-bold" onclick="openPaymentModal()">PAY NOW</button>
                        <button class="btn btn-outline-danger btn-sm" onclick="clearCart()">Clear Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MOBILE FULL-WIDTH STICKY FOOTER --}}
    <div class="d-lg-none fixed-bottom bg-white border-top shadow-lg p-3" style="z-index: 1030;">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex flex-column">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Total Due</small>
                <span class="fw-bold fs-4 text-dark lh-1">₱<span id="mobile-total-display">0.00</span></span>
            </div>
            <button
                class="btn btn-primary rounded-pill fw-bold px-4 flex-grow-1 py-2 shadow-sm d-flex justify-content-between align-items-center"
                type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileCartDrawer">
                <span>View Cart</span>
                <span class="badge bg-white text-primary rounded-pill" id="mobile-cart-count">0</span>
            </button>
        </div>
    </div>

    {{-- OFFCANVAS CART --}}
    <div class="offcanvas offcanvas-bottom rounded-top-4" tabindex="-1" id="mobileCartDrawer" style="height: 85vh;">
        <div class="offcanvas-header border-bottom py-3">
            <h5 class="offcanvas-title fw-bold">Current Order</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0 bg-light">
            {{-- Injected via JS --}}
        </div>
    </div>

    @push('modals')
        @include('cashier.partials.modals')
    @endpush

    {{-- CART TEMPLATE --}}
    <script id="cart-template" type="text/template">
                    @include('cashier.partials.cart-ui')
                </script>

    <script>
        // --- 1. CONFIGURATION ---
        const CONFIG = {
            pointsValue: Number("{{ \App\Models\Setting::where('key', 'points_conversion')->value('value') ?? 1 }}"),
            loyaltyEnabled: Number("{{ \App\Models\Setting::where('key', 'enable_loyalty')->value('value') ?? 0 }}"),
            paymongoEnabled: Number("{{ \App\Models\Setting::where('key', 'enable_paymongo')->value('value') ?? 0 }}"),
            birEnabled: Number("{{ $birEnabled ?? 0 }}"),
            taxType: "{{ \App\Models\Setting::where('key', 'tax_type')->value('value') ?? 'inclusive' }}",
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            userRole: "{{ Auth::user()->role }}",
            isRegisterOpen: {{ $isRegisterOpen ? 'true' : 'false' }} // [FIX] Loop Prevention
                    };

        function playSuccessBeep() {
            const context = new (window.AudioContext || window.webkitAudioContext)();
            const osc = context.createOscillator();
            const gain = context.createGain();
            osc.connect(gain); gain.connect(context.destination);
            osc.type = "square"; osc.frequency.value = 1500; gain.gain.value = 0.1;
            osc.start(); osc.stop(context.currentTime + 0.1);
        }
        const soundError = new Audio("https://actions.google.com/sounds/v1/alarms/spaceship_alarm.ogg");

        let cart = JSON.parse(localStorage.getItem('pos_cart')) || [];
        let currentCustomer = { id: 'walk-in', points: 0, balance: 0 };
        let html5QrCode = null;
        let isOffline = !navigator.onLine;
        let isScanning = false;
        let scanBuffer = "";
        let scanTimeout = null;

        const ALL_PRODUCTS = @json($products);

        document.addEventListener('DOMContentLoaded', () => {
            // Render Cart
            const cartHtml = document.getElementById('cart-template').innerHTML;
            document.querySelectorAll('.desktop-cart-col, #mobileCartDrawer .offcanvas-body').forEach(el => el.innerHTML = cartHtml);

            // Bind Customer (New Logic)
            // (omitted lines for brevity if unchanged, but replacing block for safety) 

            // ... (keeping standard event listeners) ...

            updateCartUI();
            updateConnectionStatus();
            window.addEventListener('online', () => { updateConnectionStatus(); syncOfflineData(); });
            window.addEventListener('offline', updateConnectionStatus);

            startLiveStockSync();

            // [FIX] Only poll if the register is actually open
            if (CONFIG.isRegisterOpen) {
                setInterval(checkRegisterStatus, 1000);
            }

            // --- CUSTOMER SELECTION LOGIC ---
            window.openCustomerModal = function () {
                new bootstrap.Modal(document.getElementById('customerSelectionModal')).show();
                setTimeout(() => document.getElementById('customer-modal-search').focus(), 500);
            };

            window.selectCustomer = function (id, name, balance) {
                // Update Global State
                currentCustomer = { id: id, balance: Number(balance), points: 0 }; // Points not passed here yet, can fetch if needed

                // Update UI (All Instances)
                document.querySelectorAll('.selected-customer-name').forEach(el => el.innerText = name);
                document.querySelectorAll('.customer-id-input').forEach(el => el.value = id);

                // Update Checks
                document.querySelectorAll('.header-check').forEach(el => el.classList.add('d-none'));
                const check = document.getElementById(`check-${id}`);
                if (check) check.classList.remove('d-none');

                // Close Modal
                const modalEl = document.getElementById('customerSelectionModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            };

            // Bind Search in Customer Modal
            document.getElementById('customer-modal-search')?.addEventListener('keyup', function () {
                const q = this.value.toLowerCase();
                document.querySelectorAll('.customer-item').forEach(item => {
                    const match = item.dataset.name.includes(q);
                    item.classList.toggle('d-none', !match);
                    item.classList.toggle('d-flex', match);
                });
            });
        });

        function checkRegisterStatus() {
            if (isOffline) return; // Don't check if offline

            fetch('/cashier/register/status', {
                headers: { 'X-CSRF-TOKEN': CONFIG.csrfToken }
            })
                .then(res => res.json())
                .then(data => {
                    // If status is closed, force reload to kick user to the "Open Register" screen
                    if (data.status === 'closed') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Register Closed',
                            text: 'The register has been closed. Reloading...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                })
                .catch(err => console.error("Status check failed", err));
        }

        function handleBatchScan(code) {
            if (!code) return;
            code = code.trim().toLowerCase();
            const product = ALL_PRODUCTS.find(p =>
                (p.sku && p.sku.toLowerCase() === code) ||
                (p.id.toString() === code)
            );

            if (product) {
                addToCart(product);
                playSuccessBeep();
                const Toast = Swal.mixin({
                    toast: true, position: 'top', showConfirmButton: false, timer: 1000,
                    timerProgressBar: false
                });
                Toast.fire({ icon: 'success', title: `${product.name} Added` });
            } else {
                playSuccessBeep();
                Swal.fire({ toast: true, position: 'top', icon: 'error', title: 'Item Not Found', timer: 1500, showConfirmButton: false });
            }
        }

        window.addToCart = function (productOrId) {
            // [FIX] Ensure we use the 'live' product object from ALL_PRODUCTS which receives sync updates
            // The onclick handler passes a static JSON snapshot from page load.
            let product = productOrId;

            // If passed object has an ID, look it up in the live array
            if (typeof productOrId === 'object' && productOrId.id) {
                const liveProduct = ALL_PRODUCTS.find(p => p.id === productOrId.id);
                if (liveProduct) product = liveProduct;
            }

            const existing = cart.find(i => i.id === product.id);
            if (existing) {
                if (existing.qty < product.current_stock) {
                    existing.qty++;
                } else {
                    soundError.play();
                    Swal.fire({ toast: true, icon: 'warning', title: 'Max Stock Reached', position: 'top-end', showConfirmButton: false, timer: 1500 });
                    return;
                }
            } else {
                if (product.current_stock > 0) {
                    cart.push({ ...product, qty: 1, max: product.current_stock });
                } else {
                    soundError.play();
                    Swal.fire({ toast: true, icon: 'error', title: 'Out of Stock', position: 'top-end', showConfirmButton: false, timer: 1500 });
                    return;
                }
            }
            updateCartUI();
        };

        window.modifyQty = function (index, change) {
            const item = cart[index];
            const newQty = item.qty + change;
            if (newQty <= 0) cart.splice(index, 1);
            else if (newQty <= item.max) item.qty = newQty;
            updateCartUI();
        };

        window.removeItem = function (index) {
            cart.splice(index, 1);
            updateCartUI();
        };

        window.clearCart = function () {
            if (cart.length === 0) return;
            Swal.fire({
                title: 'Clear Cart?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes'
            }).then((res) => { if (res.isConfirmed) { cart = []; updateCartUI(); } });
        };

        window.overridePrice = function (index) {
            const item = cart[index];
            Swal.fire({
                title: 'Override Price',
                text: `Set new price for ${item.name}`,
                input: 'number',
                inputValue: item.price,
                showCancelButton: true,
                confirmButtonText: 'Update'
            }).then((result) => {
                if (result.isConfirmed) {
                    const newPrice = parseFloat(result.value);
                    if (newPrice >= 0) {
                        item.price = newPrice;
                        item.is_overridden = true; // Flag for backend
                        updateCartUI();
                    } else {
                        Swal.fire('Invalid Price', 'Price cannot be negative.', 'error');
                    }
                }
            });
        };


        // === PRICING STRATEGIES (SOLID JS) ===
        const PricingStrategies = {
            MultiBuy: (unitPrice, qty, tiers) => {
                if (!tiers || tiers.length === 0) return unitPrice * qty;

                // Sort tiers by quantity descending (Greedy)
                const sortedTiers = [...tiers].sort((a, b) => b.quantity - a.quantity);

                let remainingQty = qty;
                let totalPrice = 0.0;

                for (const tier of sortedTiers) {
                    if (remainingQty >= tier.quantity) {
                        const numBundles = Math.floor(remainingQty / tier.quantity);
                        totalPrice += numBundles * parseFloat(tier.price);
                        remainingQty %= tier.quantity;
                    }
                }

                // Add remaining individual items
                if (remainingQty > 0) {
                    totalPrice += remainingQty * unitPrice;
                }

                return totalPrice;
            }
        };

        function calculateLineItemTotal(item) {
            if (item.is_overridden) return item.price * item.qty;

            // Find product to get tiers
            // Note: item object in cart is cloned, might not have tiers if they were added dynamically, 
            // but typically we push the whole product object. 
            // However, ALL_PRODUCTS has the truth.
            const product = ALL_PRODUCTS.find(p => p.id === item.id);
            const tiers = product ? product.pricing_tiers : [];

            if (tiers && tiers.length > 0) {
                return PricingStrategies.MultiBuy(item.price, item.qty, tiers);
            }

            return item.price * item.qty;
        }

        // === REFINED CART UI GENERATOR (Centered Controls) ===
        function updateCartUI() {
            localStorage.setItem('pos_cart', JSON.stringify(cart));

            let html = '';
            let subtotal = 0;

            if (cart.length === 0) {
                html = `
                            <div class="d-flex flex-column align-items-center justify-content-center h-75 text-muted opacity-50">
                                <i class="fas fa-shopping-cart fa-3x mb-3 text-secondary"></i>
                                <p class="fw-bold mb-0">Empty Cart</p>
                            </div>`;
            } else {
                cart.forEach((item, index) => {
                    const lineTotal = calculateLineItemTotal(item);
                    const originalTotal = item.price * item.qty;
                    const isPromo = lineTotal < originalTotal;

                    subtotal += lineTotal;

                    html += `
                                <div class="cart-item p-3 mb-2 bg-white rounded-3 border d-flex align-items-center justify-content-between shadow-sm">

                                    {{-- Text --}}
                                    <div style="flex: 1; min-width: 0; margin-right: 10px;">
                                        <div class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.95rem;">
                                            ${item.name}
                                            ${isPromo ? '<span class="badge bg-warning text-dark ms-1" style="font-size:0.6rem">PROMO</span>' : ''}
                                        </div>
                                        <div class="text-primary small fw-bold">₱${lineTotal.toFixed(2)}</div>
                                    </div>

                                    {{-- Controls --}}
                                    <div class="d-flex align-items-center bg-light rounded-pill border p-1">
                                        <button class="btn btn-sm btn-link text-dark fw-bold p-0 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; text-decoration:none;" onclick="modifyQty(${index}, -1)">−</button>
                                        <span class="fw-bold text-dark text-center" style="width: 24px; font-size: 0.9rem;">${item.qty}</span>
                                        <button class="btn btn-sm btn-link text-dark fw-bold p-0 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; text-decoration:none;" onclick="modifyQty(${index}, 1)">+</button>
                                    </div>

                                    {{-- Price Override Button (Permission Check) --}}
                                    @if(auth()->user()->hasPermission(\App\Enums\Permission::PRICE_OVERRIDE))
                                        <button class="btn btn-link text-warning p-0 ms-2" onclick="overridePrice(${index})" title="Override Price">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endif

                                    {{-- Delete --}}
                                    <button class="btn btn-link text-danger p-0 ms-2" onclick="removeItem(${index})">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>`;
                });
            }

            document.querySelectorAll('#cart-items').forEach(el => el.innerHTML = html);

            // Totals
            let grandTotal = subtotal;
            let taxAmt = 0;
            const subtotalEls = document.querySelectorAll('.subtotal-display');
            const taxRows = document.querySelectorAll('.tax-row');
            const taxEls = document.querySelectorAll('.tax-display');

            if (CONFIG.birEnabled === 1 && CONFIG.taxType === 'exclusive') {
                taxAmt = subtotal * 0.12;
                grandTotal = subtotal + taxAmt;
                taxRows.forEach(el => el.style.setProperty('display', 'flex', 'important'));
                taxEls.forEach(el => el.innerText = taxAmt.toFixed(2));
            } else {
                taxEls.forEach(el => el.innerText = '------');
                taxRows.forEach(el => el.style.display = 'none');
            }

            subtotalEls.forEach(el => el.innerText = subtotal.toFixed(2));
            document.querySelectorAll('.total-amount-display').forEach(el => el.innerText = grandTotal.toFixed(2));
            if (document.getElementById('mobile-total-display')) document.getElementById('mobile-total-display').innerText = grandTotal.toFixed(2);
            if (document.getElementById('mobile-cart-count')) document.getElementById('mobile-cart-count').innerText = cart.length;
            if (document.getElementById('modal-total')) document.getElementById('modal-total').innerText = grandTotal.toFixed(2);
        }

        // --- Search & Filter Logic ---
        function bindSearchEvents() {
            const inputs = [document.getElementById('product-search-mobile'), document.getElementById('product-search-desktop')];
            inputs.forEach(input => {
                if (!input) return;
                input.addEventListener('keyup', function () {
                    const q = this.value.toLowerCase().trim();

                    // Sync values
                    inputs.forEach(i => { if (i && i !== this) i.value = this.value; });

                    document.querySelectorAll('.product-card-wrapper').forEach(card => {
                        const match = (card.dataset.name || '').includes(q) || (card.dataset.sku || '').includes(q);
                        card.style.display = match ? 'block' : 'none';
                    });
                });
            });
        }
        bindSearchEvents();

        window.filterCategory = function (cat, btn) {
            document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
            // Activate all buttons for this category (mobile & desktop)
            document.querySelectorAll('.category-btn').forEach(b => {
                if (b.textContent.trim().toLowerCase() === btn.textContent.trim().toLowerCase()) b.classList.add('active');
            });

            document.querySelectorAll('.product-card-wrapper').forEach(card => {
                card.style.display = (cat === 'all' || card.dataset.category === cat) ? 'block' : 'none';
            });
        };

        /* --- STANDARD SCANNER & MODAL LOGIC (Unchanged) --- */
        window.html5QrcodeScanner = null;
        window.openCameraModal = function () {
            const modal = new bootstrap.Modal(document.getElementById('cameraModal'));
            modal.show();
            const config = {
                fps: 60, qrbox: { width: 300, height: 150 }, aspectRatio: 1.0,
                showTorchButtonIfSupported: true, showZoomSliderIfSupported: true, defaultZoomValueIfSupported: 1.5,
                formatsToSupport: [Html5QrcodeSupportedFormats.UPC_A, Html5QrcodeSupportedFormats.EAN_13, Html5QrcodeSupportedFormats.CODE_128],
                experimentalFeatures: { useBarCodeDetectorIfSupported: true }
            };
            if (!window.html5QrcodeScanner) {
                window.html5QrcodeScanner = new Html5QrcodeScanner("reader", config, false);
                window.html5QrcodeScanner.render(onCashierScanSuccess, (err) => { });
            }
        };
        function onCashierScanSuccess(decodedText, decodedResult) {
            const product = ALL_PRODUCTS.find(p => p.sku === decodedText);
            if (product) {
                addToCart(product); playSuccessBeep();
                Swal.fire({ toast: true, position: 'top', icon: 'success', title: `${product.name} Added`, timer: 1000, showConfirmButton: false });
                if (window.html5QrcodeScanner) { window.html5QrcodeScanner.pause(); setTimeout(() => window.html5QrcodeScanner.resume(), 1500); }
            } else {
                Swal.fire({ toast: true, position: 'top', icon: 'error', title: 'Item Not Found', timer: 1000, showConfirmButton: false });
                if (window.html5QrcodeScanner) { window.html5QrcodeScanner.pause(); setTimeout(() => window.html5QrcodeScanner.resume(), 2000); }
            }
        }
        window.stopCamera = function () {
            if (window.html5QrcodeScanner) {
                window.html5QrcodeScanner.clear().then(() => {
                    window.html5QrcodeScanner = null;
                    const modalEl = document.getElementById('cameraModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }).catch(err => console.log(err));
            }
        };
        document.getElementById('cameraModal')?.addEventListener('hidden.bs.modal', function () { window.stopCamera(); });
        window.stopCameraAndClose = function () { window.stopCamera(); };

        // --- DEBT LOGIC ---
        window.openDebtorList = function () {
            new bootstrap.Modal(document.getElementById('debtorListModal')).show();
            const listContainer = document.querySelector('#debtorListModal .list-group');
            listContainer.innerHTML = '<div class="text-center p-4 text-muted"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
            fetch("{{ route('cashier.debtors') }}")
                .then(res => res.json())
                .then(data => {
                    listContainer.innerHTML = '';
                    if (data.length === 0) { listContainer.innerHTML = '<div class="text-center p-4 text-muted">No outstanding debts found.</div>'; return; }
                    data.forEach(c => {
                        const btn = document.createElement('button');
                        btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center debtor-row';
                        btn.dataset.name = (c.name || '').toLowerCase();
                        btn.onclick = () => openDebtPaymentModal(c.id, c.name, c.balance);
                        btn.innerHTML = `<span class="fw-bold">${c.name}</span><span class="badge bg-danger rounded-pill">₱${parseFloat(c.balance).toFixed(2)}</span>`;
                        listContainer.appendChild(btn);
                    });
                }).catch(err => listContainer.innerHTML = '<div class="text-center text-danger">Failed to load.</div>');
        };
        window.filterDebtors = function () {
            const q = document.getElementById('debtor-search').value.toLowerCase();
            document.querySelectorAll('.debtor-row').forEach(row => {
                row.classList.toggle('d-none', !row.dataset.name.includes(q)); row.classList.toggle('d-flex', row.dataset.name.includes(q));
            });
        };
        window.openDebtPaymentModal = function (id, name, balance) {
            bootstrap.Modal.getInstance(document.getElementById('debtorListModal')).hide();
            document.getElementById('pay-debt-customer-id').value = id;
            document.getElementById('pay-debt-name').innerText = name;
            document.getElementById('pay-debt-balance').innerText = balance;
            document.getElementById('pay-debt-amount').value = '';
            new bootstrap.Modal(document.getElementById('debtPaymentModal')).show();
        };
        window.processDebtPayment = function () {
            const id = document.getElementById('pay-debt-customer-id').value;
            const amount = document.getElementById('pay-debt-amount').value;
            if (!amount || amount <= 0) return Swal.fire('Error', 'Enter valid amount', 'warning');
            fetch("{{ route('cashier.credit.pay') }}", {
                method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": CONFIG.csrfToken },
                body: JSON.stringify({ customer_id: id, amount: amount })
            }).then(res => res.json()).then(data => {
                if (data.success) Swal.fire('Success', 'Payment Collected!', 'success').then(() => {
                    bootstrap.Modal.getInstance(document.getElementById('debtPaymentModal')).hide();
                    startLiveStockSync(); // Refresh data
                    // Update specific debtor row if exists
                    if (currentCustomer.id == id) {
                        currentCustomer.balance -= parseFloat(amount);
                    }
                    openDebtorList(); // Refresh list
                });
                else Swal.fire('Error', data.message, 'error');
            });
        };

        // --- RETURNS LOGIC ---
        window.openReturnModal = function () { new bootstrap.Modal(document.getElementById('returnModal')).show(); };
        window.searchSaleForReturn = function () {
            const q = document.getElementById('return-search').value;
            if (!q) return Swal.fire('Error', 'Enter Sale ID', 'error');
            fetch(`{{ url('/cashier/return/search') }}?query=${q}`).then(res => res.json()).then(data => {
                if (data.success) {
                    document.getElementById('return-results').style.display = 'block';
                    const tbody = document.getElementById('return-items-body'); tbody.innerHTML = '';
                    data.items.forEach(item => {
                        if (item.available_qty > 0) {
                            tbody.innerHTML += `<tr data-id="${item.product_id}" data-price="${item.price}"><td>${item.name}</td><td>${item.sold_qty}</td><td>₱${item.price}</td><td><input type="number" class="form-control ret-qty" min="0" max="${item.available_qty}" value="0" onchange="calcRefund()"><small class="text-muted">Max: ${item.available_qty}</small></td><td><select class="form-select ret-condition"><option value="good">Good</option><option value="damaged">Damaged</option></select></td></tr>`;
                        }
                    });
                } else { Swal.fire('Not Found', data.message, 'error'); }
            });
        };
        window.calcRefund = function () {
            let total = 0;
            document.querySelectorAll('#return-items-body tr').forEach(row => {
                total += parseFloat(row.getAttribute('data-price')) * (parseInt(row.querySelector('.ret-qty').value) || 0);
            });
            document.getElementById('total-refund').innerText = total.toFixed(2);
        };
        window.submitReturn = function () {
            const saleId = document.getElementById('return-search').value;
            let items = [];
            document.querySelectorAll('#return-items-body tr').forEach(row => {
                const qty = parseInt(row.querySelector('.ret-qty').value) || 0;
                if (qty > 0) items.push({ product_id: row.getAttribute('data-id'), quantity: qty, condition: row.querySelector('.ret-condition').value });
            });
            if (items.length === 0) return Swal.fire('Error', 'No items selected', 'warning');
            fetch("{{ route('cashier.return.process') }}", {
                method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": CONFIG.csrfToken },
                body: JSON.stringify({ sale_id: saleId, items: items })
            }).then(res => res.json()).then(data => {
                if (data.success) Swal.fire('Success', 'Return processed!', 'success').then(() => {
                    bootstrap.Modal.getInstance(document.getElementById('returnModal')).hide();
                    startLiveStockSync();
                });
                else Swal.fire('Error', data.message, 'error');
            });
        };

        // --- PAYMENT LOGIC ---
        window.openPaymentModal = function () {
            if (cart.length === 0) return Swal.fire('Empty', 'Add items first', 'warning');
            document.getElementById('amount-paid').value = '';
            document.getElementById('change-display').innerText = '₱0.00';
            const cashRadio = document.getElementById('pm-cash'); const creditRadio = document.getElementById('pm-credit');
            if (currentCustomer.id === 'new') { document.getElementById('pm-cash').disabled = true; creditRadio.checked = true; }
            else if (currentCustomer.id === 'walk-in') { document.getElementById('pm-credit').disabled = true; cashRadio.checked = true; }
            else { cashRadio.disabled = false; creditRadio.disabled = false; cashRadio.checked = true; }
            toggleFlow(); new bootstrap.Modal(document.getElementById('paymentModal')).show();
            setTimeout(() => document.getElementById('amount-paid').focus(), 500);
        };
        window.toggleFlow = function () {
            const method = document.querySelector('input[name="paymethod"]:checked').value;
            document.getElementById('flow-cash').style.display = method === 'cash' ? 'block' : 'none';
            document.getElementById('flow-digital').style.display = method === 'digital' ? 'block' : 'none';
            document.getElementById('flow-credit').style.display = method === 'credit' ? 'block' : 'none';

            // Toggle New Debtor Fields based on customer type
            const newDebtorFields = document.getElementById('new-debtor-fields');
            if (newDebtorFields) {
                newDebtorFields.style.display = (currentCustomer.id === 'new') ? 'block' : 'none';
            }
        };
        window.calculateChange = function () {
            const total = parseFloat(document.getElementById('modal-total').innerText.replace(/,/g, ''));
            const paid = parseFloat(document.getElementById('amount-paid').value) || 0;
            const change = paid - total;
            const disp = document.getElementById('change-display');
            disp.innerText = change >= 0 ? '₱' + change.toFixed(2) : 'Invalid';
            disp.className = change >= 0 ? 'fw-bold text-success fs-5' : 'fw-bold text-danger fs-5';
        };
        window.processPayment = function () {
            const method = document.querySelector('input[name="paymethod"]:checked').value;
            const total = parseFloat(document.getElementById('modal-total').innerText.replace(/,/g, ''));
            if (method === 'cash') {
                const paid = parseFloat(document.getElementById('amount-paid').value) || 0;
                if (paid < total) return Swal.fire('Error', 'Insufficient Cash Payment', 'error');
            }
            if (method === 'credit') {
                const dueDate = document.getElementById('credit-due-date').value;
                if (!dueDate) return Swal.fire('Error', 'Due Date is required', 'warning');

                if (currentCustomer.id === 'new') {
                    const name = document.getElementById('credit-name').value;
                    if (!name) return Swal.fire('Error', 'Debtor Name is required', 'warning');
                }
            }

            const payload = {
                cart: cart, total_amount: total, payment_method: method, customer_id: currentCustomer.id,
                amount_paid: method === 'cash' ? document.getElementById('amount-paid').value : 0,
                reference_number: document.getElementById('reference-number')?.value,
                amount_paid: method === 'cash' ? document.getElementById('amount-paid').value : 0,
                reference_number: document.getElementById('reference-number')?.value,
                credit_details: method === 'credit' ? {
                    name: (currentCustomer.id === 'new') ? document.getElementById('credit-name')?.value : null,
                    due_date: document.getElementById('credit-due-date')?.value,
                    contact: (currentCustomer.id === 'new') ? document.getElementById('credit-contact')?.value : null,
                    address: (currentCustomer.id === 'new') ? document.getElementById('credit-address')?.value : null
                } : null
            };
            if (isOffline) { saveToOfflineQueue(payload); return; }
            Swal.showLoading();
            fetch("{{ route('cashier.store') }}", {
                method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json", "X-CSRF-TOKEN": CONFIG.csrfToken },
                body: JSON.stringify(payload)
            }).then(async res => {
                const data = await res.json(); if (!res.ok) throw new Error(data.message || 'Server Error'); return data;
            }).then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                    updateLocalStock(cart);
                    Swal.fire({ icon: 'success', title: 'Paid!', showCancelButton: true, confirmButtonText: 'Receipt', cancelButtonText: 'New Sale' })
                        .then((r) => {
                            cart = []; localStorage.removeItem('pos_cart'); updateCartUI();
                            cart = []; localStorage.removeItem('pos_cart'); updateCartUI();
                            // Reset UI
                            document.querySelectorAll('.customer-id-input').forEach(el => el.value = 'walk-in');
                            document.querySelectorAll('.selected-customer-name').forEach(el => el.innerText = 'Walk-in Customer');
                            currentCustomer = { id: 'walk-in', points: 0, balance: 0 };
                            if (r.isConfirmed) window.open(`/cashier/receipt/${data.sale_id}`, '_blank', 'width=400,height=600');
                        });
                }
            }).catch(err => {
                if (err.message.toLowerCase().includes('fetch') || err.message.toLowerCase().includes('network')) { saveToOfflineQueue(payload); }
                else { Swal.fire('Validation Error', err.message, 'warning'); }
            });
        };
        function saveToOfflineQueue(data) {
            let queue = JSON.parse(localStorage.getItem('offline_queue_sales')) || [];
            data.offline_id = Date.now(); queue.push(data);
            localStorage.setItem('offline_queue_sales', JSON.stringify(queue));
            cart = []; localStorage.removeItem('pos_cart'); updateCartUI();
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            Swal.fire('Saved Offline', 'Transaction stored locally.', 'info');
        }
        function updateConnectionStatus() { isOffline = !navigator.onLine; document.getElementById('connection-status').className = isOffline ? 'status-offline' : 'status-online'; }
        async function syncOfflineData() {
            if (isOffline) return;

            // 1. Get the queue
            let queue = JSON.parse(localStorage.getItem('offline_queue_sales')) || [];
            if (queue.length === 0) return;

            // 2. Notify User
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
            Toast.fire({ icon: 'info', title: `Syncing ${queue.length} offline records...` });

            let successCount = 0;
            let failCount = 0;
            let newQueue = [];

            // 3. Loop through transactions sequentially (Order matters!)
            for (const saleData of queue) {
                try {
                    const response = await fetch("{{ route('cashier.store') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": CONFIG.csrfToken
                        },
                        body: JSON.stringify(saleData)
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        successCount++;
                        // Optional: Print the receipt for the synced sale if needed
                        // console.log("Synced Sale ID:", data.sale_id);
                    } else {
                        console.error("Server Rejected:", data);
                        // If server rejects (e.g., validation error), we keep it to prevent data loss
                        // BUT you might want to flag it so it doesn't loop forever.
                        newQueue.push(saleData);
                        failCount++;
                    }
                } catch (error) {
                    console.error("Sync Network Error:", error);
                    // Keep in queue if connection drops again
                    newQueue.push(saleData);
                    failCount++;
                }
            }

            // 4. Update Queue with only the failed items
            localStorage.setItem('offline_queue_sales', JSON.stringify(newQueue));

            // 5. Final Report
            if (successCount > 0) {
                Toast.fire({ icon: 'success', title: `Synced ${successCount} sales successfully!` });
                // Refresh stock display since we just uploaded sales
                startLiveStockSync();
            }

            if (failCount > 0) {
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'warning',
                    title: `${failCount} sales failed to sync. Check console.`,
                    showConfirmButton: false, timer: 4000
                });
            }
        }
        function startLiveStockSync() {
            setInterval(() => {
                if (!navigator.onLine) return;
                fetch("{{ route('cashier.inventory.sync') }}").then(res => res.json()).then(data => {
                    data.forEach(item => {
                        let product = ALL_PRODUCTS.find(p => p.id === item.id);
                        if (product) {
                            product.current_stock = item.stock;
                            const badge = document.getElementById(`product-stock-${item.id}`);
                            const card = document.getElementById(`product-card-${item.id}`);
                            if (badge && card) {
                                if (item.stock <= 0) {
                                    badge.innerText = "Out of Stock"; badge.className = "badge bg-dark text-white rounded-pill px-2"; badge.style.display = "inline-block";
                                    card.style.opacity = "0.5"; card.style.pointerEvents = "none"; card.classList.add("bg-secondary");
                                } else {
                                    badge.innerText = `${item.stock}`; badge.style.display = item.stock <= (product.reorder_point ?? 10) ? 'inline-block' : 'none';
                                    card.style.opacity = "1"; card.style.pointerEvents = "auto"; card.classList.remove("bg-secondary");
                                }
                            }
                        }
                    });
                }).catch(err => console.error(err));
            }, 5000);
        }
        function updateLocalStock(soldItems) {
            soldItems.forEach(item => {
                const stockEl = document.getElementById(`product-stock-${item.id}`);
                const cardEl = document.getElementById(`product-card-${item.id}`);
                const globalProduct = ALL_PRODUCTS.find(p => p.id === item.id);
                if (stockEl && globalProduct) {
                    let newStock = globalProduct.current_stock - item.qty;
                    if (newStock < 0) newStock = 0;
                    globalProduct.current_stock = newStock;
                    stockEl.innerText = `${newStock}`;
                    stockEl.style.display = 'inline-block';
                    if (newStock === 0 && cardEl) {
                        cardEl.style.opacity = '0.5'; cardEl.style.pointerEvents = 'none'; cardEl.classList.add('bg-secondary');
                        stockEl.innerText = 'Out of Stock'; stockEl.className = 'badge bg-dark text-white rounded-pill px-2';
                    }
                }
            });
        }

        // --- 13. PAYMONGO INTEGRATION (GCash) ---
        // --- PAYMONGO & PAYMENT LOGIC ---
        let paymentCheckInterval = null;
        let isProcessing = false; // Prevents double submission

        window.generatePaymentLink = function () {
            const total = parseFloat(document.getElementById('modal-total').innerText.replace(/,/g, ''));
            const btn = document.getElementById('btn-gen-qr');
            const completeBtn = document.getElementById('btn-complete-payment');

            if (total <= 0) return Swal.fire('Error', 'Amount must be greater than 0', 'error');

            // UI Loading State
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Generating...';

            // HIDE Complete button to prevent premature clicking (Fixes 422 Error)
            if (completeBtn) completeBtn.style.display = 'none';

            fetch("{{ route('payment.create') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": CONFIG.csrfToken
                },
                body: JSON.stringify({ amount: total })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Switch UI to QR Mode
                        document.getElementById('paymongo-controls').style.display = 'none';
                        document.getElementById('paymongo-qr-area').style.display = 'block';

                        document.getElementById('qrcode-container').innerHTML = "";
                        new QRCode(document.getElementById("qrcode-container"), {
                            text: data.checkout_url,
                            width: 200,
                            height: 200
                        });

                        startPaymentPolling(data.id);
                    } else {
                        Swal.fire('API Error', data.message, 'error');
                        resetPayMongoUI();
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'Connection failed.', 'error');
                    resetPayMongoUI();
                });
        };

        function startPaymentPolling(id) {
            if (paymentCheckInterval) clearInterval(paymentCheckInterval);

            paymentCheckInterval = setInterval(() => {
                fetch(`/cashier/payment/check/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'paid') {
                            clearInterval(paymentCheckInterval);
                            playSuccessBeep();

                            document.getElementById('reference-number').value = id;

                            // Auto-submit
                            processPayment();
                        }
                    })
                    .catch(err => console.error("Polling error:", err));
            }, 3000);
        }

        function resetPayMongoUI() {
            const btn = document.getElementById('btn-gen-qr');
            const completeBtn = document.getElementById('btn-complete-payment');

            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-qrcode me-2"></i> Generate G-Cash QR';
            }
            if (completeBtn) completeBtn.style.display = 'block'; // Show button again

            document.getElementById('paymongo-controls').style.display = 'block';
            document.getElementById('paymongo-qr-area').style.display = 'none';

            if (paymentCheckInterval) clearInterval(paymentCheckInterval);
        }

        // Process Payment with Debounce (Fixes Race Conditions)
        window.processPayment = function () {
            if (isProcessing) return; // Stop if already running

            const method = document.querySelector('input[name="paymethod"]:checked').value;
            const total = parseFloat(document.getElementById('modal-total').innerText.replace(/,/g, ''));

            if (method === 'cash') {
                const paid = parseFloat(document.getElementById('amount-paid').value) || 0;
                if (paid < total) return Swal.fire('Error', 'Insufficient Cash Payment', 'error');
            }

            const payload = {
                cart: cart,
                total_amount: total,
                payment_method: method,
                customer_id: currentCustomer.id,
                amount_paid: method === 'cash' ? document.getElementById('amount-paid').value : 0,
                reference_number: document.getElementById('reference-number')?.value,
                credit_details: method === 'credit' ? {
                    name: document.getElementById('credit-name')?.value,
                    due_date: document.getElementById('credit-due-date')?.value,
                    contact: document.getElementById('credit-contact')?.value,
                    address: document.getElementById('credit-address')?.value
                } : null
            };

            if (isOffline) { saveToOfflineQueue(payload); return; }

            isProcessing = true; // LOCK
            Swal.showLoading();

            fetch("{{ route('cashier.store') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "Accept": "application/json", "X-CSRF-TOKEN": CONFIG.csrfToken },
                body: JSON.stringify(payload)
            })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Server Error');
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        Swal.close();
                        bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                        updateLocalStock(cart);

                        // Calculate Change for Display
                        const paidAmt = parseFloat(payload.amount_paid) || 0;
                        const changeAmt = (payload.payment_method === 'cash') ? (paidAmt - payload.total_amount) : 0;
                        const custName = document.querySelector(`#customer-id option[value="${currentCustomer.id}"]`)?.text || 'Walk-in Customer';

                        // Show Custom Receipt Modal
                        showReceiptModal(data, payload.total_amount, changeAmt, custName);
                    }
                })
                .catch(err => {
                    if (err.message.toLowerCase().includes('fetch') || err.message.toLowerCase().includes('network')) {
                        saveToOfflineQueue(payload);
                    } else {
                        Swal.fire('Validation Error', err.message, 'warning');
                    }
                })
                .finally(() => {
                    isProcessing = false; // UNLOCK
                });
        };

        // === RECEIPT MODAL LOGIC ===
        function showReceiptModal(data, total, change, customerName) {
            // Populate Data
            document.getElementById('receipt-modal-amount').innerText = parseFloat(total).toLocaleString('en-US', { minimumFractionDigits: 2 });
            document.getElementById('receipt-modal-change').innerText = parseFloat(change).toLocaleString('en-US', { minimumFractionDigits: 2 });
            document.getElementById('receipt-modal-customer').innerText = customerName;
            document.getElementById('receipt-modal-ref').innerText = data.sale_id;

            // Setup Buttons
            const printBtn = document.getElementById('btn-receipt-print');
            printBtn.onclick = () => window.open(`/cashier/receipt/${data.sale_id}`, '_blank', 'width=400,height=600');

            const newSaleBtn = document.getElementById('btn-receipt-new');
            newSaleBtn.onclick = () => {
                bootstrap.Modal.getInstance(document.getElementById('receiptOverviewModal')).hide();
                resetCashierState();
            };

            // Show Modal
            new bootstrap.Modal(document.getElementById('receiptOverviewModal'), { backdrop: 'static', keyboard: false }).show();
        }

        function resetCashierState() {
            cart = [];
            localStorage.removeItem('pos_cart');
            updateCartUI();

            document.querySelectorAll('.customer-id-input').forEach(el => el.value = 'walk-in');
            document.querySelectorAll('.selected-customer-name').forEach(el => el.innerText = 'Walk-in Customer');
            currentCustomer = { id: 'walk-in', points: 0, balance: 0 };
            document.getElementById('product-search-desktop').focus();
        }

        // Clean up if modal is closed
        document.getElementById('paymentModal')?.addEventListener('hidden.bs.modal', function () {
            if (paymentCheckInterval) clearInterval(paymentCheckInterval);
            resetPayMongoUI();
            isProcessing = false;
        });


        // === SECURITY: ADMIN AUTH WRAPPER ===
        async function requestAdminAuth(callback) {
            if (['admin', 'manager'].includes(CONFIG.userRole)) {
                callback();
                return;
            }

            const { value: password } = await Swal.fire({
                title: 'Admin Authorization',
                text: 'This action requires Admin approval.',
                input: 'password',
                inputLabel: 'Enter Admin Password',
                inputPlaceholder: 'Password',
                showCancelButton: true,
                confirmButtonText: 'Verify',
                confirmButtonColor: '#dc3545', // Red for "Security"
                cancelButtonColor: '#6c757d',
                allowOutsideClick: false,
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off'
                }
            });

            if (password) {
                Swal.showLoading();

                fetch("{{ route('cashier.verify_admin') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CONFIG.csrfToken
                    },
                    body: JSON.stringify({ password: password })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Success! Run the restricted function
                            Swal.close();
                            callback();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Access Denied',
                                text: 'Incorrect Admin Password',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', 'Verification failed. Check connection.', 'error');
                    });
            }
        }
    </script>

    {{-- 3. RECEIPT OVERVIEW MODAL --}}
    <div class="modal fade" id="receiptOverviewModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
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
            <div class="modal-content border-0 shadow-lg rounded-4">
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

    <script>
        // --- CASH REGISTER LOGIC ---
        let currentSessionId = null;

        document.addEventListener('DOMContentLoaded', function () {
            checkRegisterStatus();
        });

        function checkRegisterStatus() {
            // [FEATURE FLAG] Skip check if feature is disabled
            if (CONFIG.registerLogsEnabled != '1') {
                const openBtnDesktop = document.getElementById('btn-close-register-desktop');
                const openBtnMobile = document.getElementById('btn-close-register-mobile');
                // Ensure buttons are hidden
                if (openBtnDesktop) openBtnDesktop.classList.add('d-none');
                if (openBtnMobile) openBtnMobile.classList.add('d-none');
                return;
            }

            fetch('/cashier/register/status')
                .then(res => res.json())
                .then(data => {
                    // Set global CONFIG.isRegisterOpen based on status
                    CONFIG.isRegisterOpen = (data.status === 'open');

                    const openBtnDesktop = document.getElementById('btn-close-register-desktop');
                    const openBtnMobile = document.getElementById('btn-close-register-mobile');

                    // [FIX] Role-Based Open Register UI
                    if (!CONFIG.isRegisterOpen) {
                        const openModalEl = document.getElementById('openRegisterModal');
                        if (CONFIG.userRole === 'admin') {
                            // Admin: Allow Opening
                            const modal = new bootstrap.Modal(openModalEl, { backdrop: 'static', keyboard: false });

                            // Inject "Back to Dashboard" link if not present
                            const form = openModalEl.querySelector('form');
                            if (form && !document.getElementById('btn-back-dashboard')) {
                                const backBtn = document.createElement('div');
                                backBtn.className = 'text-center mt-3';
                                backBtn.innerHTML = `<a href="/admin/dashboard" id="btn-back-dashboard" class="text-muted text-decoration-none small fw-bold"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>`;
                                form.appendChild(backBtn);
                            }

                            modal.show();
                        } else {
                            // Cashier: Show "Goodbye" Message & Logout
                            const modalBody = openModalEl.querySelector('.modal-body');
                            const modalFooter = openModalEl.querySelector('.modal-footer'); // Safely check if exists
                            const modalHeader = openModalEl.querySelector('.modal-header');

                            if (modalHeader) modalHeader.style.display = 'none'; // Hide header
                            if (modalFooter) modalFooter.style.display = 'none';

                            modalBody.innerHTML = `
                                            <div class="text-center py-5">
                                                <i class="fas fa-moon fa-4x text-primary mb-4 opacity-75"></i>
                                                <h4 class="fw-bold text-dark">Register is closed</h4>
                                                <p class="text-muted mb-4">Great work today! Rest well. See you tomorrow.</p>

                                                <div class="d-flex justify-content-center gap-2">
                                                    <button onclick="window.location.reload()" class="btn btn-outline-primary rounded-pill px-4 fw-bold py-2 shadow-sm">
                                                        <i class="fas fa-sync-alt me-2"></i> Reload Store
                                                    </button>

                                                    <form action="/logout" method="POST" class="d-inline">
                                                        <input type="hidden" name="_token" value="${CONFIG.csrfToken}">
                                                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm py-2">
                                                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        `;

                            const modal = new bootstrap.Modal(openModalEl, { backdrop: 'static', keyboard: false });
                            modal.show();
                        }
                        if (openBtnDesktop) openBtnDesktop.classList.add('d-none');
                        if (openBtnMobile) openBtnMobile.classList.add('d-none');
                    } else {
                        // Register Open
                        currentSessionId = data.session.id;
                        if (openBtnDesktop) openBtnDesktop.classList.remove('d-none');
                        if (openBtnMobile) openBtnMobile.classList.remove('d-none');

                        // Hide Modal if open (in case of manual refresh during modal open)
                        const existingModal = bootstrap.Modal.getInstance(document.getElementById('openRegisterModal'));
                        if (existingModal) existingModal.hide();
                    }
                })
                .catch(err => console.error('Status Check Failed', err));
        }

        function submitOpenRegister(e) {
            e.preventDefault();
            const amount = document.getElementById('opening_float').value;

            fetch('/cashier/register/open', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ opening_amount: amount })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Register Opened',
                            text: 'You may now process transactions.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload(); // Reload to clear modal and update UI
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Failed to open register', 'error');
                    }
                });
        }

        function showCloseRegisterModal() {
            document.getElementById('close_session_id').value = currentSessionId;
            const modal = new bootstrap.Modal(document.getElementById('closeRegisterModal'));
            modal.show();
        }

        function submitCloseRegister(e) {
            e.preventDefault();
            const amount = document.getElementById('closing_amount').value;
            const notes = document.getElementById('closing_notes').value;
            const sessionId = document.getElementById('close_session_id').value;

            // 1. CONFIRMATION STEP (Safety Net)
            Swal.fire({
                title: `Confirm Closing Count`,
                html: `You entered actual cash: <h2 class="text-danger fw-bold">₱${parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</h2><br>Are you sure this is correct?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, Close Register',
                cancelButtonText: 'Re-count'
            }).then((result) => {
                if (result.isConfirmed) {
                    // 2. SUBMIT
                    fetch('/cashier/register/close', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            session_id: sessionId,
                            closing_amount: amount,
                            notes: notes
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Closed', 'Register session closed successfully.', 'success')
                                    .then(() => {
                                        location.reload(); // Will trigger Open Modal again
                                    });
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        }) // Close .then(data => ...)
                        .catch(err => {
                            console.error("Close Register Error:", err);
                            Swal.fire('Error', 'An unexpected error occurred. Please check console.', 'error');
                        });
                }
            });
        }

        // [NEW] Refresh Logic with Delay
        function refreshWithDelay() {
            const btn = document.getElementById('btn-refresh-status');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Checking...';

            setTimeout(() => {
                window.location.reload();
            }, 10000); // 10 seconds delay
        }
    </script>
@endsection