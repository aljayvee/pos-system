@extends('cashier.layout')

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
    html, body { 
        overflow-x: hidden; 
        width: 100%; 
        position: relative;
        background-color: var(--bg-app); 
        font-family: 'Inter', system-ui, sans-serif;
    }

    /* SEARCH BAR */
    .search-wrapper {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        display: flex; align-items: center; padding: 6px;
        transition: box-shadow 0.2s;
    }
    .search-wrapper:focus-within {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .search-input {
        border: none; background: transparent; font-size: 0.95rem;
        padding: 8px 12px; width: 100%; outline: none;
    }
    
    /* CATEGORY PILLS */
    .category-scroll {
        -ms-overflow-style: none; scrollbar-width: none; 
        padding: 4px 2px 12px 2px;
        white-space: nowrap; /* Prevent wrapping */
    }
    .category-scroll::-webkit-scrollbar { display: none; }
    
    .category-btn {
        border: 1px solid var(--border-color);
        background: white; color: var(--text-sub);
        font-weight: 600; font-size: 0.85rem;
        padding: 8px 16px; border-radius: 50px;
        transition: all 0.2s;
    }
    .category-btn.active {
        background: var(--primary); color: white; border-color: var(--primary);
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
    }

    /* PRODUCT CARD */
    .product-card-wrapper { padding: 4px; } /* Tighter padding for mobile */
    
    .product-item {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px; /* Slightly smaller radius for mobile */
        cursor: pointer; position: relative; overflow: hidden; height: 100%;
        display: flex; flex-direction: column;
        transition: transform 0.1s;
    }
    .product-item:active { transform: scale(0.96); }

    .product-img-box {
        height: 100px; /* Compact height */
        background: #f8fafc;
        display: flex; align-items: center; justify-content: center;
        border-bottom: 1px solid #f1f5f9; color: #cbd5e1;
    }
    .product-content { padding: 10px; display: flex; flex-direction: column; flex-grow: 1; }

    /* STOCK BADGE */
    .stock-badge {
        position: absolute; top: 6px; right: 6px;
        font-size: 0.65rem; font-weight: 700;
        background: rgba(255,255,255,0.95);
        border: 1px solid #fecaca; color: #dc2626;
        z-index: 10;
    }

    /* DESKTOP LAYOUT */
    @media (min-width: 992px) {
        .mobile-footer { display: none !important; }
        .desktop-cart-col { display: block; height: calc(100vh - 40px); position: sticky; top: 20px; }
        .product-grid-container { height: 78vh; }
        .container-fluid { padding-left: 1.5rem; padding-right: 1.5rem; }
    }

    /* MOBILE LAYOUT (< 992px) */
    @media (max-width: 991px) {
        .desktop-cart-col { display: none; }
        .product-grid-container { height: auto !important; padding-bottom: 100px; }
        
        /* Reset Container Padding for edge-to-edge feel on small screens */
        .container-fluid { padding-left: 1rem; padding-right: 1rem; }
        
        .sticky-tools {
            position: sticky; top: 0; z-index: 99;
            background: var(--bg-app);
            padding-top: 10px; padding-bottom: 5px;
        }
    }
</style>

<div id="connection-status" class="status-online" style="height:3px; position:fixed; top:0; width:100%; z-index:9999;"></div>

<div class="container-fluid">
    <div class="row g-3">
        
        {{-- LEFT: PRODUCT AREA --}}
        <div class="col-lg-8 col-12 pt-3">
            
            {{-- 1. Sticky Tools Header --}}
            <div class="sticky-tools">
                <div class="d-flex gap-2 mb-3 align-items-center">
                    {{-- Search --}}
                    <div class="search-wrapper flex-grow-1">
                        <i class="fas fa-search text-muted ms-2"></i>
                        <input type="text" id="product-search" class="search-input" placeholder="Search...">
                        <button class="btn btn-sm text-primary" onclick="openCameraModal()"><i class="fas fa-barcode fa-lg"></i></button>
                    </div>
                    
                    {{-- Compact Action Buttons --}}
                    {{-- Changed onclick to use requestAdminAuth() --}}
                    <button class="btn btn-white border shadow-sm rounded-3 px-3 text-secondary" onclick="requestAdminAuth(openDebtorList)">
                        <i class="fas fa-book text-danger"></i>
                    </button>
                    <button class="btn btn-white border shadow-sm rounded-3 px-3 text-secondary" onclick="requestAdminAuth(openReturnModal)">
                        <i class="fas fa-undo-alt text-warning"></i>
                    </button>
                </div>

                {{-- 2. Category Filter --}}
                <div class="category-scroll d-flex gap-2 overflow-auto">
                    <button class="category-btn active" onclick="filterCategory('all', this)">All</button>
                    @foreach($categories as $cat)
                        <button class="category-btn" onclick="filterCategory('{{ strtolower($cat->name) }}', this)">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- 3. Product Grid --}}
            <div class="product-grid-container flex-grow-1 overflow-auto">
                <div class="row g-2" id="product-list">
                    @foreach($products as $product)
                    {{-- Adjusted Column Sizes for Phablet (Tecno Pova 5) --}}
                    <div class="col-xl-3 col-lg-4 col-4 product-card-wrapper" 
                            data-name="{{ strtolower($product->name) }}" 
                            data-sku="{{ $product->sku }}"
                            data-category="{{ strtolower($product->category->name ?? '') }}">
                        
                        <div class="product-item" id="product-card-{{ $product->id }}" onclick='addToCart(@json($product))'>
                            {{-- Stock Badge --}}
                            <span class="badge stock-badge rounded-pill px-2" 
                                id="product-stock-{{ $product->id }}"
                                style="display: {{ $product->current_stock <= ($product->reorder_point ?? 10) ? 'inline-block' : 'none' }};">
                                {{ $product->current_stock }}
                            </span>

                            <div class="product-img-box">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <i class="fas fa-box fa-3x opacity-25"></i>
                                @endif
                            </div>
                            
                            <div class="product-content">
                                <h6 class="fw-bold text-dark lh-1 text-truncate mb-1" style="font-size: 0.9rem;">{{ $product->name }}</h6>
                                <div class="mt-auto">
                                    <span class="text-primary fw-bolder d-block" style="font-size: 0.95rem;">₱{{ number_format($product->price, 2) }}</span>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $product->unit ?? 'pc' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- RIGHT: CART (Desktop Only) --}}
        <div class="col-lg-4 desktop-cart-col">
            {{-- Injected via JS --}}
        </div>
    </div>
</div>

{{-- MOBILE FOOTER (Floating Centered Island) --}}
<div class="mobile-footer" style="position: fixed; bottom: 20px; left: 0; right: 0; display: flex; justify-content: center; z-index: 1000; pointer-events: none;">
    <div class="shadow-lg bg-dark text-white rounded-pill px-4 py-3 d-flex align-items-center justify-content-between gap-4" style="pointer-events: auto; min-width: 300px; max-width: 90%;">
        
        <div class="d-flex flex-column" style="line-height: 1;">
            <small class="text-white-50 text-uppercase fw-bold" style="font-size: 0.65rem;">Total Due</small>
            <span class="fw-bold fs-4">₱<span id="mobile-total-display">0.00</span></span>
        </div>

        <button class="btn btn-primary rounded-pill fw-bold px-4" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileCartDrawer">
            View Cart 
            <span class="badge bg-white text-primary ms-2 rounded-circle" id="mobile-cart-count">0</span>
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

@include('cashier.partials.modals')

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
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
        document.querySelectorAll('.desktop-cart-col, .offcanvas-body').forEach(el => el.innerHTML = cartHtml);

        // Bind Customer
        document.querySelectorAll('#customer-id').forEach(sel => {
            sel.addEventListener('change', function() {
                document.querySelectorAll('#customer-id').forEach(s => s.value = this.value);
                const opt = this.options[this.selectedIndex];
                currentCustomer = { 
                    id: this.value, 
                    balance: parseFloat(opt.dataset.balance || 0), 
                    points: parseInt(opt.dataset.points || 0) 
                };
                if (currentCustomer.balance > 0) {
                     Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: `Debt: ₱${currentCustomer.balance}`, timer: 3000, showConfirmButton: false });
                }
                updateCartUI(); 
            });
        });

        // Focus Search
        const searchInput = document.getElementById('product-search');
        if (searchInput && window.innerWidth > 768) searchInput.focus();

        // Scanner Listener
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' && e.target.id !== 'product-search') return;
            if (e.target.tagName === 'TEXTAREA') return;

            if (e.key === 'Enter') {
                if (scanBuffer.length > 1) { 
                    const finalCode = (document.activeElement.id === 'product-search') 
                                      ? document.getElementById('product-search').value 
                                      : scanBuffer;
                    handleBatchScan(finalCode);
                    if(document.activeElement.id === 'product-search') document.getElementById('product-search').value = '';
                }
                scanBuffer = "";
            } else if (e.key.length === 1) {
                scanBuffer += e.key;
                clearTimeout(scanTimeout);
                scanTimeout = setTimeout(() => scanBuffer = "", 200);
            }
        });

        updateCartUI();
        updateConnectionStatus();
        window.addEventListener('online', () => { updateConnectionStatus(); syncOfflineData(); });
        window.addEventListener('offline', updateConnectionStatus);
        
        startLiveStockSync(); 
    });

    function handleBatchScan(code) {
        if(!code) return;
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

    window.addToCart = function(product) {
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

    window.modifyQty = function(index, change) {
        const item = cart[index];
        const newQty = item.qty + change;
        if (newQty <= 0) cart.splice(index, 1);
        else if (newQty <= item.max) item.qty = newQty;
        updateCartUI();
    };

    window.removeItem = function(index) {
        cart.splice(index, 1);
        updateCartUI();
    };
    
    window.clearCart = function() {
        if(cart.length === 0) return;
        Swal.fire({
            title: 'Clear Cart?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes'
        }).then((res) => { if(res.isConfirmed) { cart = []; updateCartUI(); } });
    };

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
                subtotal += item.price * item.qty;
                html += `
                <div class="cart-item p-3 mb-2 bg-white rounded-3 border d-flex align-items-center justify-content-between shadow-sm">
                    
                    {{-- Text --}}
                    <div style="flex: 1; min-width: 0; margin-right: 10px;">
                        <div class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.95rem;">${item.name}</div>
                        <div class="text-primary small fw-bold">₱${(item.price * item.qty).toFixed(2)}</div>
                    </div>

                    {{-- Controls --}}
                    <div class="d-flex align-items-center bg-light rounded-pill border p-1">
                        <button class="btn btn-sm btn-link text-dark fw-bold p-0 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; text-decoration:none;" onclick="modifyQty(${index}, -1)">−</button>
                        <span class="fw-bold text-dark text-center" style="width: 24px; font-size: 0.9rem;">${item.qty}</span>
                        <button class="btn btn-sm btn-link text-dark fw-bold p-0 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; text-decoration:none;" onclick="modifyQty(${index}, 1)">+</button>
                    </div>
                    
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
        if(document.getElementById('mobile-total-display')) document.getElementById('mobile-total-display').innerText = grandTotal.toFixed(2);
        if(document.getElementById('mobile-cart-count')) document.getElementById('mobile-cart-count').innerText = cart.length;
        if(document.getElementById('modal-total')) document.getElementById('modal-total').innerText = grandTotal.toFixed(2);
    }

    // --- Search & Filter Logic ---
    document.getElementById('product-search').addEventListener('keyup', function() {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.product-card-wrapper').forEach(card => {
            const match = (card.dataset.name || '').includes(q) || (card.dataset.sku || '').includes(q);
            card.style.display = match ? 'block' : 'none';
        });
    });

    window.filterCategory = function(cat, btn) {
        document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.product-card-wrapper').forEach(card => {
            card.style.display = (cat === 'all' || card.dataset.category === cat) ? 'block' : 'none';
        });
    };

    /* --- STANDARD SCANNER & MODAL LOGIC (Unchanged) --- */
    window.html5QrcodeScanner = null;
    window.openCameraModal = function() {
        const modal = new bootstrap.Modal(document.getElementById('cameraModal'));
        modal.show();
        const config = { 
            fps: 60, qrbox: { width: 300, height: 150 }, aspectRatio: 1.0, 
            showTorchButtonIfSupported: true, showZoomSliderIfSupported: true, defaultZoomValueIfSupported: 1.5,
            formatsToSupport: [ Html5QrcodeSupportedFormats.UPC_A, Html5QrcodeSupportedFormats.EAN_13, Html5QrcodeSupportedFormats.CODE_128 ],
            experimentalFeatures: { useBarCodeDetectorIfSupported: true }
        };
        if (!window.html5QrcodeScanner) {
            window.html5QrcodeScanner = new Html5QrcodeScanner("reader", config, false);
            window.html5QrcodeScanner.render(onCashierScanSuccess, (err) => {});
        }
    };
    function onCashierScanSuccess(decodedText, decodedResult) {
        const product = ALL_PRODUCTS.find(p => p.sku === decodedText);
        if (product) {
            addToCart(product); playSuccessBeep();
            Swal.fire({ toast: true, position: 'top', icon: 'success', title: `${product.name} Added`, timer: 1000, showConfirmButton: false });
            if(window.html5QrcodeScanner) { window.html5QrcodeScanner.pause(); setTimeout(() => window.html5QrcodeScanner.resume(), 1500); }
        } else {
            Swal.fire({ toast: true, position: 'top', icon: 'error', title: 'Item Not Found', timer: 1000, showConfirmButton: false });
            if(window.html5QrcodeScanner) { window.html5QrcodeScanner.pause(); setTimeout(() => window.html5QrcodeScanner.resume(), 2000); }
        }
    }
    window.stopCamera = function() {
        if (window.html5QrcodeScanner) {
            window.html5QrcodeScanner.clear().then(() => {
                window.html5QrcodeScanner = null;
                const modalEl = document.getElementById('cameraModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if(modal) modal.hide();
            }).catch(err => console.log(err));
        }
    };
    document.getElementById('cameraModal')?.addEventListener('hidden.bs.modal', function () { window.stopCamera(); });
    window.stopCameraAndClose = function() { window.stopCamera(); };

    // --- DEBT LOGIC ---
    window.openDebtorList = function() {
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
    window.filterDebtors = function() {
        const q = document.getElementById('debtor-search').value.toLowerCase();
        document.querySelectorAll('.debtor-row').forEach(row => {
            row.classList.toggle('d-none', !row.dataset.name.includes(q)); row.classList.toggle('d-flex', row.dataset.name.includes(q));
        });
    };
    window.openDebtPaymentModal = function(id, name, balance) {
        bootstrap.Modal.getInstance(document.getElementById('debtorListModal')).hide();
        document.getElementById('pay-debt-customer-id').value = id;
        document.getElementById('pay-debt-name').innerText = name;
        document.getElementById('pay-debt-balance').innerText = balance;
        document.getElementById('pay-debt-amount').value = '';
        new bootstrap.Modal(document.getElementById('debtPaymentModal')).show();
    };
    window.processDebtPayment = function() {
        const id = document.getElementById('pay-debt-customer-id').value;
        const amount = document.getElementById('pay-debt-amount').value;
        if(!amount || amount <= 0) return Swal.fire('Error', 'Enter valid amount', 'warning');
        fetch("{{ route('cashier.credit.pay') }}", {
            method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": CONFIG.csrfToken },
            body: JSON.stringify({ customer_id: id, amount: amount })
        }).then(res => res.json()).then(data => {
            if(data.success) Swal.fire('Success', 'Payment Collected!', 'success').then(() => location.reload());
            else Swal.fire('Error', data.message, 'error');
        });
    };

    // --- RETURNS LOGIC ---
    window.openReturnModal = function() { new bootstrap.Modal(document.getElementById('returnModal')).show(); };
    window.searchSaleForReturn = function() {
        const q = document.getElementById('return-search').value;
        if (!q) return Swal.fire('Error', 'Enter Sale ID', 'error');
        fetch(`{{ url('/cashier/return/search') }}?query=${q}`).then(res => res.json()).then(data => {
            if (data.success) {
                document.getElementById('return-results').style.display = 'block';
                const tbody = document.getElementById('return-items-body'); tbody.innerHTML = '';
                data.items.forEach(item => {
                    if(item.available_qty > 0) {
                        tbody.innerHTML += `<tr data-id="${item.product_id}" data-price="${item.price}"><td>${item.name}</td><td>${item.sold_qty}</td><td>₱${item.price}</td><td><input type="number" class="form-control ret-qty" min="0" max="${item.available_qty}" value="0" onchange="calcRefund()"><small class="text-muted">Max: ${item.available_qty}</small></td><td><select class="form-select ret-condition"><option value="good">Good</option><option value="damaged">Damaged</option></select></td></tr>`;
                    }
                });
            } else { Swal.fire('Not Found', data.message, 'error'); }
        });
    };
    window.calcRefund = function() {
        let total = 0;
        document.querySelectorAll('#return-items-body tr').forEach(row => {
            total += parseFloat(row.getAttribute('data-price')) * (parseInt(row.querySelector('.ret-qty').value) || 0);
        });
        document.getElementById('total-refund').innerText = total.toFixed(2);
    };
    window.submitReturn = function() {
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
            if (data.success) Swal.fire('Success', 'Return processed!', 'success').then(() => location.reload());
            else Swal.fire('Error', data.message, 'error');
        });
    };

    // --- PAYMENT LOGIC ---
    window.openPaymentModal = function() {
        if(cart.length === 0) return Swal.fire('Empty', 'Add items first', 'warning');
        document.getElementById('amount-paid').value = '';
        document.getElementById('change-display').innerText = '₱0.00';
        const cashRadio = document.getElementById('pm-cash'); const creditRadio = document.getElementById('pm-credit');
        if (currentCustomer.id === 'new') { document.getElementById('pm-cash').disabled = true; creditRadio.checked = true; } 
        else if (currentCustomer.id === 'walk-in') { document.getElementById('pm-credit').disabled = true; cashRadio.checked = true; } 
        else { cashRadio.disabled = false; creditRadio.disabled = false; cashRadio.checked = true; }
        toggleFlow(); new bootstrap.Modal(document.getElementById('paymentModal')).show();
        setTimeout(() => document.getElementById('amount-paid').focus(), 500);
    };
    window.toggleFlow = function() {
        const method = document.querySelector('input[name="paymethod"]:checked').value;
        document.getElementById('flow-cash').style.display = method === 'cash' ? 'block' : 'none';
        document.getElementById('flow-digital').style.display = method === 'digital' ? 'block' : 'none';
        document.getElementById('flow-credit').style.display = method === 'credit' ? 'block' : 'none';
    };
    window.calculateChange = function() {
        const total = parseFloat(document.getElementById('modal-total').innerText.replace(/,/g,''));
        const paid = parseFloat(document.getElementById('amount-paid').value) || 0;
        const change = paid - total;
        const disp = document.getElementById('change-display');
        disp.innerText = change >= 0 ? '₱' + change.toFixed(2) : 'Invalid';
        disp.className = change >= 0 ? 'fw-bold text-success fs-5' : 'fw-bold text-danger fs-5';
    };
    window.processPayment = function() {
        const method = document.querySelector('input[name="paymethod"]:checked').value;
        const total = parseFloat(document.getElementById('modal-total').innerText.replace(/,/g,''));
        if (method === 'cash') {
            const paid = parseFloat(document.getElementById('amount-paid').value) || 0;
            if (paid < total) return Swal.fire('Error', 'Insufficient Cash Payment', 'error');
        } 
        const payload = {
            cart: cart, total_amount: total, payment_method: method, customer_id: currentCustomer.id,
            amount_paid: method === 'cash' ? document.getElementById('amount-paid').value : 0,
            reference_number: document.getElementById('reference-number')?.value,
            credit_details: method === 'credit' ? { name: document.getElementById('credit-name')?.value, due_date: document.getElementById('credit-due-date')?.value, contact: document.getElementById('credit-contact')?.value, address: document.getElementById('credit-address')?.value } : null
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
                    document.getElementById('customer-id').value = 'walk-in'; currentCustomer = { id: 'walk-in', points: 0, balance: 0 };
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
    function syncOfflineData() { if(!isOffline && localStorage.getItem('offline_queue_sales')) Swal.fire({toast:true, title:'Syncing...', position:'top-end', timer:2000, showConfirmButton:false}); }
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

    window.generatePaymentLink = function() {
        const total = parseFloat(document.getElementById('modal-total').innerText.replace(/,/g, ''));
        const btn = document.getElementById('btn-gen-qr');
        const completeBtn = document.getElementById('btn-complete-payment');

        if (total <= 0) return Swal.fire('Error', 'Amount must be greater than 0', 'error');

        // UI Loading State
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Generating...';
        
        // HIDE Complete button to prevent premature clicking (Fixes 422 Error)
        if(completeBtn) completeBtn.style.display = 'none';

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
        
        if(btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-qrcode me-2"></i> Generate G-Cash QR';
        }
        if(completeBtn) completeBtn.style.display = 'block'; // Show button again
        
        document.getElementById('paymongo-controls').style.display = 'block';
        document.getElementById('paymongo-qr-area').style.display = 'none';
        
        if (paymentCheckInterval) clearInterval(paymentCheckInterval);
    }

    // Process Payment with Debounce (Fixes Race Conditions)
    window.processPayment = function() {
        if (isProcessing) return; // Stop if already running
        
        const method = document.querySelector('input[name="paymethod"]:checked').value;
        const total = parseFloat(document.getElementById('modal-total').innerText.replace(/,/g,''));
        
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
                bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                updateLocalStock(cart);
                
                Swal.fire({
                    icon: 'success', title: 'Paid!', showCancelButton: true, confirmButtonText: 'Receipt', cancelButtonText: 'New Sale'
                }).then((r) => {
                    cart = []; localStorage.removeItem('pos_cart'); updateCartUI(); 
                    document.getElementById('customer-id').value = 'walk-in'; 
                    currentCustomer = { id: 'walk-in', points: 0, balance: 0 };
                    if (r.isConfirmed) window.open(`/cashier/receipt/${data.sale_id}`, '_blank', 'width=400,height=600');
                });
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

    // Clean up if modal is closed
    document.getElementById('paymentModal')?.addEventListener('hidden.bs.modal', function () {
        if (paymentCheckInterval) clearInterval(paymentCheckInterval);
        resetPayMongoUI();
        isProcessing = false;
    });


    // === SECURITY: ADMIN AUTH WRAPPER ===
async function requestAdminAuth(callback) {
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
@endsection