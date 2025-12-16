@extends('cashier.layout')

@section('content')
{{-- Libraries --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* UI VARIABLES */
    :root { 
        --primary: #4f46e5; 
        --primary-light: #e0e7ff;
        --text-dark: #1e293b;
        --text-gray: #64748b;
    }

    /* PRODUCT CARD REDESIGN */
    .product-card-wrapper { transition: all 0.2s ease; }
    
    .product-item {
        background: white;
        border: 0;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    .product-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(79, 70, 229, 0.15); /* Soft Indigo Glow */
    }
    .product-item:active { transform: scale(0.98); }

    .product-icon-area {
        background: #f8fafc;
        border-radius: 12px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        color: #94a3b8;
    }

    /* CATEGORY CHIPS */
    .category-filter {
        border: 1px solid #e2e8f0;
        background: white;
        color: var(--text-gray);
        font-weight: 500;
        transition: all 0.2s;
    }
    .category-filter:hover { background: #f1f5f9; }
    .category-filter.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }

    /* MOBILE FOOTER */
    .mobile-footer {
        position: fixed; bottom: 0; left: 0; right: 0;
        background: white;
        padding: 16px 24px;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
        z-index: 1040;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
        display: flex; justify-content: space-between; align-items: center;
    }

    /* SCROLLBAR HIDE */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* === CRITICAL FIX FOR CART SCROLLING === */
    @media (min-width: 992px) {
        .mobile-footer { display: none; }
        
        /* Force the cart column to have a fixed height matching the viewport minus header */
        .desktop-cart-col { 
            display: block; 
            height: calc(100vh - 110px); 
            position: sticky;
            top: 90px;
        }
    }

    @media (max-width: 991px) {
        .desktop-cart-col { display: none; }
        .product-grid-container { height: auto !important; padding-bottom: 100px; }
    }
</style>

<div id="connection-status" class="status-online" style="height:3px; position:fixed; top:0; width:100%; z-index:9999;"></div>

<div class="container-fluid py-4 px-3 px-md-4">
    <div class="row g-4">
        
        {{-- LEFT: PRODUCT AREA --}}
        <div class="col-lg-8 col-12">
            
            {{-- 1. Search Bar & Tools --}}
            <div class="d-flex flex-column flex-md-row gap-3 mb-4">
                {{-- Search Group --}}
                <div class="input-group shadow-sm rounded-4 overflow-hidden border-0 flex-grow-1">
                    <span class="input-group-text bg-white border-0 ps-4 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" id="product-search" class="form-control border-0 py-3 bg-white" placeholder="Search products..." style="box-shadow: none;">
                    <button class="btn btn-white border-0 pe-4 text-primary" onclick="openCameraModal()" title="Scan Barcode">
                        <i class="fas fa-barcode fa-lg"></i>
                    </button>
                </div>

                {{-- Desktop Actions --}}
                <div class="d-none d-lg-flex gap-2">
                    <button class="btn btn-white shadow-sm rounded-4 px-3 fw-bold text-secondary border-0" onclick="openDebtorList()">
                        <i class="fas fa-hand-holding-usd text-danger me-2"></i> Debt
                    </button>
                    <button class="btn btn-white shadow-sm rounded-4 px-3 fw-bold text-secondary border-0" onclick="openReturnModal()">
                        <i class="fas fa-undo text-warning me-2"></i> Return
                    </button>
                </div>
            </div>

            {{-- 2. Categories --}}
            <div class="d-flex gap-2 overflow-auto pb-2 mb-3 no-scrollbar align-items-center">
                <button class="btn rounded-pill px-4 py-2 category-filter active" onclick="filterCategory('all', this)">All</button>
                @foreach($categories as $cat)
                    <button class="btn rounded-pill px-4 py-2 category-filter" 
                            style="white-space: nowrap;" 
                            onclick="filterCategory('{{ strtolower($cat->name) }}', this)">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>

            {{-- 3. Product Grid --}}
            <div class="product-grid-container flex-grow-1 overflow-auto pe-1" style="height: 72vh;">
                <div class="row g-3" id="product-list">
                    @foreach($products as $product)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-6 product-card-wrapper" 
                            data-name="{{ strtolower($product->name) }}" 
                            data-sku="{{ $product->sku }}"
                            data-category="{{ strtolower($product->category->name ?? '') }}">
                        
                        {{-- 1. ADD ID TO THE CARD CONTAINER --}}
                        <div class="product-item p-3 d-flex flex-column" 
                            id="product-card-{{ $product->id }}" 
                            onclick='addToCart(@json($product))'>
                            {{-- Stock Badge --}}
                            {{-- 2. STOCK BADGE (Always render, control visibility with style) --}}
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1" 
                                        id="product-stock-{{ $product->id }}"
                                        style="font-size: 0.7rem; display: {{ $product->current_stock <= ($product->reorder_point ?? 10) ? 'inline-block' : 'none' }};">
                                        {{ $product->current_stock }} left
                                    </span>
                                </div>

                            <div class="product-icon-area">
                                <i class="fas fa-box fa-2x opacity-50"></i>
                            </div>
                            
                            <div class="mt-auto">
                                <h6 class="fw-bold text-dark lh-sm text-truncate mb-1">{{ $product->name }}</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-primary fw-bolder">₱{{ number_format($product->price, 2) }}</span>
                                    <small class="text-muted" style="font-size: 0.75rem;">{{ $product->unit ?? 'pc' }}</small>
                                </div>
                            </div>
                        </div>

                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- RIGHT: CART (Desktop) --}}
        {{-- IMPORTANT: Logic is handled by the CSS above for .desktop-cart-col --}}
        <div class="col-lg-4 desktop-cart-col">
            {{-- Injected via JS --}}
        </div>
    </div>
</div>

{{-- MOBILE FOOTER --}}
<div class="mobile-footer">
    <div>
        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">Total Due</small>
        <h2 class="fw-bold text-dark m-0">₱<span id="mobile-total-display">0.00</span></h2>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!--<button class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px; " onclick="clearCart()">
            <i class="fas fa-trash-alt text-danger"></i>
        </button>-->
        <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-lg" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileCartDrawer">
            View Cart <span class="badge bg-white text-primary ms-2 rounded-pill" id="mobile-cart-count">0</span>
        </button>
    </div>
</div>

{{-- OFFCANVAS CART --}}
<div class="offcanvas offcanvas-bottom rounded-top-4" tabindex="-1" id="mobileCartDrawer" style="height: 85vh;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold"><i class="fas fa-shopping-bag me-2 text-primary"></i>Your Order</h5>
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

    // --- 2. SOUNDS (Base64) ---
    // Sci-Fi Beep for Success
    // --- 2. SOUNDS (Web Audio API) ---
function playSuccessBeep() {
    const context = new (window.AudioContext || window.webkitAudioContext)();
    const osc = context.createOscillator();
    const gain = context.createGain();

    osc.connect(gain);
    gain.connect(context.destination);

    osc.type = "square";        // Sharp digital sound
    osc.frequency.value = 1500; // High pitch
    gain.gain.value = 0.1;      // Lower volume
    
    osc.start();
    osc.stop(context.currentTime + 0.1); // Short duration
}

const soundError = new Audio("https://actions.google.com/sounds/v1/alarms/spaceship_alarm.ogg");
    // Alarm for Error
    //const soundError = new Audio("https://actions.google.com/sounds/v1/alarms/spaceship_alarm.ogg");

    // --- 3. STATE MANAGEMENT ---
    let cart = JSON.parse(localStorage.getItem('pos_cart')) || [];
    let currentCustomer = { id: 'walk-in', points: 0, balance: 0 };
    let html5QrCode = null; // Changed from Scanner to Html5Qrcode Class
    let isOffline = !navigator.onLine;
    let isScanning = false; // Debounce flag
    let scanBuffer = ""; // For USB Scanner
    let scanTimeout = null;
    
    const ALL_PRODUCTS = @json($products);

    // --- 4. INITIALIZATION ---
    document.addEventListener('DOMContentLoaded', () => {
        // Render Cart
        const cartHtml = document.getElementById('cart-template').innerHTML;
        document.querySelectorAll('.desktop-cart-col, .offcanvas-body').forEach(el => el.innerHTML = cartHtml);

        // Bind Customer Selectors
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

        // Focus Search on Desktop
        const searchInput = document.getElementById('product-search');
        if (searchInput && window.innerWidth > 768) searchInput.focus();

        // --- GLOBAL SCANNER LISTENER (USB/Keyboard) ---
        document.addEventListener('keydown', (e) => {
            // Ignore if typing in input (except search, which handles its own enter)
            if (e.target.tagName === 'INPUT' && e.target.id !== 'product-search') return;
            if (e.target.tagName === 'TEXTAREA') return;

            if (e.key === 'Enter') {
                if (scanBuffer.length > 1) { 
                    // Use search input value if focused, else buffer
                    const finalCode = (document.activeElement.id === 'product-search') 
                                      ? document.getElementById('product-search').value 
                                      : scanBuffer;
                    
                    handleBatchScan(finalCode); // Trigger unified scan
                    
                    if(document.activeElement.id === 'product-search') {
                        document.getElementById('product-search').value = '';
                    }
                }
                scanBuffer = "";
            } else if (e.key.length === 1) {
                scanBuffer += e.key;
                clearTimeout(scanTimeout);
                scanTimeout = setTimeout(() => scanBuffer = "", 200); // Reset if too slow (human typing)
            }
        });

        updateCartUI();
        updateConnectionStatus();
        window.addEventListener('online', () => { updateConnectionStatus(); syncOfflineData(); });
        window.addEventListener('offline', updateConnectionStatus);
    });

    // --- 5. UNIFIED SCAN HANDLER (Batch Logic) ---
    function handleBatchScan(code) {
        if(!code) return;
        code = code.trim().toLowerCase();

        // Match by SKU (Barcode) OR ID
        const product = ALL_PRODUCTS.find(p => 
            (p.sku && p.sku.toLowerCase() === code) || 
            (p.id.toString() === code)
        );

        if (product) {
            addToCart(product);
            
            // Sound & Visual Feedback
            soundBeep.currentTime = 0; soundBeep.play().catch(e=>{});
            
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

    // --- 6. CORE POS LOGIC ---
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

    function updateCartUI() {
        localStorage.setItem('pos_cart', JSON.stringify(cart));
        
        let html = '';
        let subtotal = 0;
        
        // 1. Build Cart HTML
        if (cart.length === 0) {
            html = `<div class="text-center text-muted mt-5"><i class="fas fa-basket-shopping fa-3x opacity-25"></i><p>Cart is empty</p></div>`;
        } else {
            cart.forEach((item, index) => {
                subtotal += item.price * item.qty;
                html += `
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <div style="width:40%" class="fw-bold text-truncate">${item.name}</div>
                    <div class="bg-light rounded px-2">
                        <button class="btn btn-sm fw-bold text-primary" onclick="modifyQty(${index}, -1)">-</button>
                        <span class="mx-2 fw-bold small">${item.qty}</span>
                        <button class="btn btn-sm fw-bold text-primary" onclick="modifyQty(${index}, 1)">+</button>
                    </div>
                    <div class="fw-bold text-end" style="width:20%">₱${(item.price * item.qty).toFixed(2)}</div>
                    <button class="btn btn-sm text-danger ms-2" onclick="removeItem(${index})">&times;</button>
                </div>`;
            });
        }

        // Update BOTH the desktop sidebar and mobile offcanvas list
        document.querySelectorAll('#cart-items').forEach(el => el.innerHTML = html);

        // 2. TAX VISIBILITY LOGIC
        let grandTotal = subtotal;
        let taxAmt = 0;
        
        // --- FIXED: Use querySelectorAll to update BOTH desktop and mobile views ---
        const subtotalEls = document.querySelectorAll('.subtotal-display');
        const taxRows = document.querySelectorAll('.tax-row'); 
        const taxEls = document.querySelectorAll('.tax-display');

        if (CONFIG.birEnabled === 1 && CONFIG.taxType === 'exclusive') {
            taxAmt = subtotal * 0.12; 
            grandTotal = subtotal + taxAmt;
            
            // Show Tax Row
            taxRows.forEach(el => el.style.setProperty('display', 'flex', 'important'));
            taxEls.forEach(el => el.innerText = taxAmt.toFixed(2));
        } else {
            // Hide Tax Row
            taxEls.forEach(el => el.innerText = '------');
            taxRows.forEach(el => el.style.display = 'none');
            
        }

        // 3. Update Subtotal Text
        subtotalEls.forEach(el => el.innerText = subtotal.toFixed(2));

        // 4. Update Totals
        document.querySelectorAll('.total-amount-display').forEach(el => el.innerText = grandTotal.toFixed(2));
        if(document.getElementById('mobile-total-display')) document.getElementById('mobile-total-display').innerText = grandTotal.toFixed(2);
        if(document.getElementById('mobile-cart-count')) document.getElementById('mobile-cart-count').innerText = cart.length + ' Items';
        if(document.getElementById('modal-total')) document.getElementById('modal-total').innerText = grandTotal.toFixed(2);
    }

    // --- 7. SEARCH & FILTER ---
    document.getElementById('product-search').addEventListener('keyup', function() {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.product-card-wrapper').forEach(card => {
            // Updated to also search data-sku
            const match = (card.dataset.name || '').includes(q) || (card.dataset.sku || '').includes(q);
            card.style.display = match ? 'block' : 'none';
        });
    });

    window.filterCategory = function(cat, btn) {
        document.querySelectorAll('.category-filter').forEach(b => { 
            b.classList.remove('btn-dark'); b.classList.add('btn-light', 'border'); 
        });
        btn.classList.remove('btn-light', 'border'); btn.classList.add('btn-dark');
        
        document.querySelectorAll('.product-card-wrapper').forEach(card => {
            card.style.display = (cat === 'all' || card.dataset.category === cat) ? 'block' : 'none';
        });
    };

    // --- 7. ROBUST CAMERA SCANNER (Ported from Add Product View) ---
    // We use the same High-Performance Config here for consistent behavior
    
    // Global variable to hold the scanner instance
    window.html5QrcodeScanner = null;

    window.openCameraModal = function() {
        // 1. Show the Modal
        const modal = new bootstrap.Modal(document.getElementById('cameraModal'));
        modal.show();

        // 2. HIGH PERFORMANCE CONFIGURATION (From create.blade.php)
        const config = { 
            fps: 60, // Fast scanning (20 frames per second)
            qrbox: { width: 300, height: 150 }, // Rectangular box for 1D barcodes
            aspectRatio: 1.0, 
            
            // UI Controls
            showTorchButtonIfSupported: true, // Flashlight for dark items
            showZoomSliderIfSupported: true,  // Zoom Slider (Vital for small barcodes)
            defaultZoomValueIfSupported: 1.5, // Default 1.5x zoom to help focus

            // STRICTLY define formats for speed and accuracy
            formatsToSupport: [ 
                Html5QrcodeSupportedFormats.UPC_A, 
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8, 
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39
            ],
            
            // Use Browser Native API (Faster & more robust)
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true
            }
        };

        // 3. Initialize Scanner if not already running
        if (!window.html5QrcodeScanner) {
            // "reader" is the ID of the div in your modal
            window.html5QrcodeScanner = new Html5QrcodeScanner("reader", config, false);
            
            // Render with specific success callback
            window.html5QrcodeScanner.render(onCashierScanSuccess, (err) => { 
                // Ignore frame parse errors (console noise) 
            });
        }
    };

    function onCashierScanSuccess(decodedText, decodedResult) {
        // 1. Search for the product by SKU
        // Note: We use the global ALL_PRODUCTS variable defined in index.blade.php
        const product = ALL_PRODUCTS.find(p => p.sku === decodedText);

        if (product) {
            // 2. Add to Cart (Batch Mode)
            addToCart(product);
            // ADD THIS LINE:
            playSuccessBeep();
            
            // 3. Visual Feedback (Toast)
            Swal.fire({
                toast: true, position: 'top', icon: 'success', 
                title: `${product.name} Added`, 
                timer: 1000, showConfirmButton: false 
            });

            // 4. Batch Logic: Pause briefly to prevent adding the same item 50 times in 1 second
            if(window.html5QrcodeScanner) {
                window.html5QrcodeScanner.pause();
                setTimeout(() => window.html5QrcodeScanner.resume(), 1500);
            }

        } else {
            // Error Feedback
            Swal.fire({
                toast: true, position: 'top', icon: 'error', 
                title: 'Item Not Found', 
                timer: 1000, showConfirmButton: false 
            });
            
            // Pause briefly on error too so user can see the message
            if(window.html5QrcodeScanner) {
                window.html5QrcodeScanner.pause();
                setTimeout(() => window.html5QrcodeScanner.resume(), 2000);
            }
        }
    }

    // Explicit Stop Function for the Close Button
    window.stopCamera = function() {
        if (window.html5QrcodeScanner) {
            window.html5QrcodeScanner.clear().then(() => {
                window.html5QrcodeScanner = null;
                // Force hide modal if needed
                const modalEl = document.getElementById('cameraModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if(modal) modal.hide();
            }).catch(err => console.log("Failed to clear scanner", err));
        }
    };

    // Cleanup when modal is closed via clicking outside/ESC
    document.getElementById('cameraModal')?.addEventListener('hidden.bs.modal', function () {
        window.stopCamera();
    });

    // Cleanup when modal is closed via clicking outside/ESC
    document.getElementById('cameraModal').addEventListener('hidden.bs.modal', function () {
        window.stopCamera();
    });
    
    window.stopCameraAndClose = function() {
        if(html5QrCode) {
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                bootstrap.Modal.getInstance(document.getElementById('cameraModal')).hide();
            }).catch(() => bootstrap.Modal.getInstance(document.getElementById('cameraModal')).hide());
        } else {
            bootstrap.Modal.getInstance(document.getElementById('cameraModal')).hide();
        }
    };

    // --- 9. DEBT LOGIC (RESTORED) ---
    window.openDebtorList = function() {
        new bootstrap.Modal(document.getElementById('debtorListModal')).show();
        const listContainer = document.querySelector('#debtorListModal .list-group');
        listContainer.innerHTML = '<div class="text-center p-4 text-muted"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

        fetch("{{ route('cashier.debtors') }}")
            .then(res => res.json())
            .then(data => {
                listContainer.innerHTML = ''; 
                if (data.length === 0) {
                    listContainer.innerHTML = '<div class="text-center p-4 text-muted">No outstanding debts found.</div>';
                    return;
                }
                data.forEach(c => {
                    const btn = document.createElement('button');
                    btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center debtor-row';
                    btn.dataset.name = (c.name || '').toLowerCase();
                    btn.onclick = () => openDebtPaymentModal(c.id, c.name, c.balance);
                    btn.innerHTML = `<span class="fw-bold">${c.name}</span><span class="badge bg-danger rounded-pill">₱${parseFloat(c.balance).toFixed(2)}</span>`;
                    listContainer.appendChild(btn);
                });
            })
            .catch(err => listContainer.innerHTML = '<div class="text-center text-danger">Failed to load.</div>');
    };

    window.filterDebtors = function() {
        const q = document.getElementById('debtor-search').value.toLowerCase();
        document.querySelectorAll('.debtor-row').forEach(row => {
            row.classList.toggle('d-none', !row.dataset.name.includes(q));
            row.classList.toggle('d-flex', row.dataset.name.includes(q));
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
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": CONFIG.csrfToken },
            body: JSON.stringify({ customer_id: id, amount: amount })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) Swal.fire('Success', 'Payment Collected!', 'success').then(() => location.reload());
            else Swal.fire('Error', data.message, 'error');
        });
    };

    // --- 10. RETURNS LOGIC (RESTORED) ---
    window.openReturnModal = function() { new bootstrap.Modal(document.getElementById('returnModal')).show(); };

    window.searchSaleForReturn = function() {
        const q = document.getElementById('return-search').value;
        if (!q) return Swal.fire('Error', 'Enter Sale ID', 'error');

        fetch(`{{ url('/cashier/return/search') }}?query=${q}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('return-results').style.display = 'block';
                    const tbody = document.getElementById('return-items-body');
                    tbody.innerHTML = '';
                    data.items.forEach(item => {
                        if(item.available_qty > 0) {
                            tbody.innerHTML += `
                                <tr data-id="${item.product_id}" data-price="${item.price}">
                                    <td>${item.name}</td>
                                    <td>${item.sold_qty}</td>
                                    <td>₱${item.price}</td>
                                    <td>
                                        <input type="number" class="form-control ret-qty" min="0" max="${item.available_qty}" value="0" onchange="calcRefund()">
                                        <small class="text-muted">Max: ${item.available_qty}</small>
                                    </td>
                                    <td>
                                        <select class="form-select ret-condition"><option value="good">Good</option><option value="damaged">Damaged</option></select>
                                    </td>
                                </tr>`;
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
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": CONFIG.csrfToken },
            body: JSON.stringify({ sale_id: saleId, items: items })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) Swal.fire('Success', 'Return processed!', 'success').then(() => location.reload());
            else Swal.fire('Error', data.message, 'error');
        });
    };

    // --- 11. PAYMENT LOGIC (RESTORED) ---
    window.openPaymentModal = function() {
        if(cart.length === 0) return Swal.fire('Empty', 'Add items first', 'warning');
        
        document.getElementById('amount-paid').value = '';
        document.getElementById('change-display').innerText = '₱0.00';
        
        // Logic for Customer Credit restrictions
        const cashRadio = document.getElementById('pm-cash');
        const creditRadio = document.getElementById('pm-credit');
        
        if (currentCustomer.id === 'new') {
            document.getElementById('pm-cash').disabled = true;
            creditRadio.checked = true;
        } else if (currentCustomer.id === 'walk-in') {
            document.getElementById('pm-credit').disabled = true;
            cashRadio.checked = true;
        } else {
            cashRadio.disabled = false; creditRadio.disabled = false;
            cashRadio.checked = true;
        }

        toggleFlow();
        new bootstrap.Modal(document.getElementById('paymentModal')).show();
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
                // --- ADD THIS LINE HERE ---
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
        });
    };

    // --- 12. OFFLINE SYNC (RESTORED) ---
    function saveToOfflineQueue(data) {
        let queue = JSON.parse(localStorage.getItem('offline_queue_sales')) || [];
        data.offline_id = Date.now(); queue.push(data);
        localStorage.setItem('offline_queue_sales', JSON.stringify(queue));
        cart = []; localStorage.removeItem('pos_cart'); updateCartUI();
        bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
        Swal.fire('Saved Offline', 'Transaction stored locally.', 'info');
    }

    function updateConnectionStatus() {
       isOffline = !navigator.onLine;
        document.getElementById('connection-status').className = isOffline ? 'status-offline' : 'status-online';
    }

    function syncOfflineData() { 
        if(!isOffline && localStorage.getItem('offline_queue_sales')) 
            Swal.fire({toast:true, title:'Syncing...', position:'top-end', timer:2000, showConfirmButton:false}); 
    }

    // --- OPTIMISTIC UI UPDATE (INSTANT STOCK DEDUCTION) ---
function updateLocalStock(soldItems) {
    soldItems.forEach(item => {
        // 1. Find elements
        const stockEl = document.getElementById(`product-stock-${item.id}`);
        const cardEl = document.getElementById(`product-card-${item.id}`);

        // 2. Locate the product in your global list to update its data
        const globalProduct = ALL_PRODUCTS.find(p => p.id === item.id);

        if (stockEl && globalProduct) {
            // Calculate new stock
            let newStock = globalProduct.current_stock - item.qty;
            if (newStock < 0) newStock = 0;

            // Update Global Variable (So next click checks new stock)
            globalProduct.current_stock = newStock;

            // Update Visual Badge
            stockEl.innerText = `${newStock} left`;
            stockEl.style.display = 'inline-block'; // Show badge (even if it was hidden)

            // 3. Visual "Out of Stock" State
            if (newStock === 0 && cardEl) {
                cardEl.style.opacity = '0.5';
                cardEl.style.pointerEvents = 'none'; // Disable clicks
                cardEl.classList.add('bg-secondary'); // Optional gray background
                stockEl.innerText = 'Out of Stock';
                stockEl.className = 'badge bg-dark text-white rounded-pill px-2 py-1';
            }
        }
    });
}
</script>
@endsection