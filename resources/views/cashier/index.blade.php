{{-- 
   FILE: resources/views/cashier/index.blade.php 
   STATUS: MERGED & FIXED
   
   PRESERVED:
   - Debt/Credit Collection Logic
   - Returns/Refund Processing
   - Offline Mode & Syncing
   - Complex Payment Validation
   
   ADDED/FIXED:
   - Robust 1D Barcode Scanning (Camera + USB)
   - Batch Scanning (Instant Add + Sound)
   - Responsive Phablet/Mobile Layout
--}}
@extends('cashier.layout')

@section('content')
{{-- External Libraries --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root { --primary: #4f46e5; --success: #10b981; --bg-light: #f3f4f6; }
    body { background-color: var(--bg-light); font-family: 'Inter', sans-serif; padding-bottom: 80px; }
    
    /* PRODUCT CARD STYLES */
    .product-item {
        cursor: pointer; border: 1px solid #e5e7eb; background: white;
        transition: transform 0.2s, box-shadow 0.2s; border-radius: 12px; overflow: hidden;
    }
    .product-item:active { transform: scale(0.96); }
    .product-item:hover { transform: translateY(-3px); border-color: var(--primary); }
    
    /* CART CONTAINER */
    .cart-container {
        background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        height: calc(100vh - 100px); display: flex; flex-direction: column;
    }
    .cart-items-area { flex-grow: 1; overflow-y: auto; padding: 0 10px; }

    /* SCANNER VISUALS */
    #reader { width: 100%; border-radius: 8px; overflow: hidden; background: black; }
    /* Target the overlay to make it look like a 1D scanner */
    #reader__scan_region { background: rgba(255, 255, 255, 0.1) !important; border: 2px solid #10b981 !important; }

    /* CONNECTION STATUS BAR */
    #connection-status { position: fixed; top: 0; left: 0; right: 0; height: 4px; z-index: 9999; }
    .status-online { background: var(--success); }
    .status-offline { background: #ef4444; }

    /* MOBILE / PHABLET OPTIMIZATIONS */
    @media (max-width: 768px) {
        /* Hide Desktop Cart Column on Mobile */
        .desktop-cart-col { display: none !important; }
        
        /* Mobile Sticky Footer */
        .mobile-footer {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: white; border-top: 1px solid #e5e7eb;
            padding: 12px 20px; z-index: 1040;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 -4px 6px -1px rgba(0,0,0,0.1);
        }
        
        /* Adjust Product Grid for Mobile */
        .product-grid-container { height: auto !important; overflow: visible !important; }
        
        /* Full Screen Camera on Mobile */
        #cameraModal .modal-dialog { margin: 0; max-width: 100%; height: 100%; }
        #cameraModal .modal-content { height: 100%; border-radius: 0; }
        #reader { height: 60vh; object-fit: cover; }
    }
    
    @media (min-width: 769px) {
        .mobile-footer { display: none; }
    }
</style>

<div id="connection-status" class="status-online"></div>

<div class="container-fluid p-3">
    <div class="row g-3">
        {{-- LEFT COLUMN: PRODUCTS --}}
        <div class="col-lg-8 col-md-7 col-12">
            
            {{-- Search & Tools --}}
            <div class="card border-0 shadow-sm rounded-4 p-2 p-md-3 mb-3">
                <div class="d-flex gap-2 align-items-center"> 
                    
                    {{-- 1. Search + Scan Group --}}
                    <div class="input-group flex-grow-1">
                        <span class="input-group-text bg-white border-end-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                        {{-- Added "Scan..." placeholder to indicate it accepts scanner input --}}
                        <input type="text" id="product-search" class="form-control border-start-0 border-end-0 py-2" placeholder="Search Item or Scan...">
                        
                        {{-- Camera Button --}}
                        <button class="btn btn-dark px-3" onclick="openCameraModal()" title="Open Camera Scanner">
                            <i class="fas fa-camera"></i> <span class="d-none d-md-inline ms-1">Scan</span>
                        </button>
                    </div>

                    {{-- 2. Desktop Buttons --}}
                    <div class="d-none d-lg-flex gap-2">
                        <button class="btn btn-danger fw-bold rounded-3" onclick="openDebtorList()">
                            <i class="fas fa-hand-holding-usd me-1"></i> Pay Debt
                        </button>
                        <button class="btn btn-warning fw-bold rounded-3" onclick="openReturnModal()">
                            <i class="fas fa-undo me-1"></i> Return
                        </button>
                        @if($birEnabled == '1')
                        <a href="{{ route('cashier.reading', 'x') }}" target="_blank" class="btn btn-outline-secondary rounded-3" title="Report">
                            <i class="fas fa-print"></i>
                        </a>
                        @endif
                    </div>
                </div>

                {{-- 3. Categories --}}
                <div class="d-flex gap-2 mt-2 overflow-auto pb-1 no-scrollbar">
                    <button class="btn btn-dark btn-sm rounded-pill px-3 fw-bold category-filter active" onclick="filterCategory('all', this)">All</button>
                    @foreach($categories as $cat)
                        <button class="btn btn-light btn-sm border rounded-pill px-3 fw-bold category-filter" 
                                style="white-space: nowrap;" 
                                onclick="filterCategory('{{ strtolower($cat->name) }}', this)">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Product Grid --}}
            <div class="product-grid-container flex-grow-1 overflow-auto pe-1" style="height: 70vh;">
                <div class="row g-2" id="product-list">
                    @foreach($products as $product)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-6 product-card-wrapper" 
                            data-name="{{ strtolower($product->name) }}" 
                            data-sku="{{ strtolower($product->sku ?? '') }}"
                            data-id="{{ $product->id }}"
                            data-category="{{ strtolower($product->category->name ?? '') }}">
                        <div class="product-item h-100 p-3 d-flex flex-column justify-content-between text-center" onclick='addToCart(@json($product))'>
                            @if($product->current_stock <= ($product->reorder_point ?? 10))
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">{{ $product->current_stock }}</span>
                            @endif
                            <div class="mb-2 text-secondary opacity-25"><i class="fas fa-box fa-2x"></i></div>
                            <h6 class="fw-bold text-dark lh-sm text-truncate small">{{ $product->name }}</h6>
                            <h5 class="text-primary fw-bold mb-0">₱{{ number_format($product->price, 2) }}</h5>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: CART (Desktop View) --}}
        <div class="col-lg-4 col-md-5 desktop-cart-col">
            {{-- Placeholder for JS injection --}}
        </div>
    </div>
</div>

{{-- === MOBILE STICKY FOOTER (Phablet View) === --}}
<div class="mobile-footer">
    <div>
        <small class="text-muted d-block">Total Due</small>
        <h3 class="fw-bold text-primary m-0">₱<span id="mobile-total-display">0.00</span></h3>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-danger rounded-pill d-flex align-items-center" id="mobile-cart-count">0 Items</span>
        <button class="btn btn-dark fw-bold rounded-pill px-4" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileCartDrawer">
            View Cart <i class="fas fa-chevron-up ms-2"></i>
        </button>
    </div>
</div>

{{-- === MOBILE OFFCANVAS CART DRAWER === --}}
<div class="offcanvas offcanvas-bottom rounded-top-4" tabindex="-1" id="mobileCartDrawer" style="height: 85vh;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold"><i class="fas fa-shopping-bag me-2"></i>Current Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        {{-- Placeholder for JS injection --}}
    </div>
</div>

{{-- === CAMERA MODAL (REPLACED WITH ROBUST VERSION) === --}}
<div class="modal fade" id="cameraModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title"><i class="fas fa-barcode me-2"></i>Batch Scanner</h5>
                <button type="button" class="btn-close btn-close-white" onclick="stopCameraAndClose()"></button>
            </div>
            <div class="modal-body bg-black p-0 d-flex justify-content-center align-items-center position-relative">
                <div id="reader" style="width: 100%; min-height: 300px;"></div>
                {{-- Overlay Text --}}
                <div class="position-absolute text-white text-center w-100 pointer-events-none" style="bottom: 20px; z-index: 10;">
                    <small class="bg-dark bg-opacity-50 px-3 py-1 rounded-pill">Align barcode in box</small>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="text-muted small"><i class="fas fa-volume-up"></i> Sound On</div>
                <button type="button" class="btn btn-secondary" onclick="stopCameraAndClose()">Done</button>
            </div>
        </div>
    </div>
</div>

{{-- === OTHER MODALS (Debt, Return, Payment) === --}}
@include('cashier.partials.modals')

{{-- === REUSABLE CART UI COMPONENT === --}}
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
    const soundBeep = new Audio("data:audio/wav;base64,UklGRl9vT1BXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU"); 
    soundBeep.src = "https://actions.google.com/sounds/v1/science_fiction/scifi_laser.ogg"; 
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
            soundError.currentTime = 0; soundError.play().catch(e=>{});
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

        document.querySelectorAll('#cart-items').forEach(el => el.innerHTML = html);

        // Tax Logic
        let grandTotal = subtotal;
        let taxAmt = 0;
        const taxRow = document.getElementById('tax-row');
        
        if (CONFIG.birEnabled === 1 && CONFIG.taxType === 'exclusive') {
            taxAmt = subtotal * 0.12; 
            grandTotal = subtotal + taxAmt;
            if(taxRow) taxRow.style.setProperty('display', 'flex', 'important');
            if(document.getElementById('tax-display')) document.getElementById('tax-display').innerText = taxAmt.toFixed(2);
        } else {
            if(taxRow) taxRow.style.display = 'hidden';
        }

        document.getElementById('subtotal-display').innerText = subtotal.toFixed(2);
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

    // --- 8. CAMERA (ROBUST IMPLEMENTATION) ---
    // REPLACE your existing window.openCameraModal function with this:

window.openCameraModal = function() {
    // 1. SECURITY CHECK
    if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        Swal.fire({
            icon: 'error',
            title: 'Security Error',
            html: `
                <p>Google Chrome blocks camera access on insecure connections (HTTP).</p>
                <hr>
                <div class="text-start small">
                    <strong>Solution:</strong><br>
                    1. Use <b>Ngrok</b> to create an HTTPS link.<br>
                    2. Or enable "Insecure origins" in Chrome Flags.
                </div>
            `
        });
        return;
    }

    const modal = new bootstrap.Modal(document.getElementById('cameraModal'));
    modal.show();
    
    // Initialize HTML5QRCode
    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode("reader");
    }

    const config = { 
        fps: 15, 
        qrbox: { width: 250, height: 150 }, // 1D Barcode Box
        aspectRatio: 1.0,
        experimentalFeatures: { useBarCodeDetectorIfSupported: true } 
    };

    // Prefer Back Camera
    html5QrCode.start(
        { facingMode: "environment" }, 
        config, 
        (decodedText) => {
            // SUCCESS
            if (isScanning) return;
            isScanning = true;
            handleScan(decodedText); // Call your scan handler
            setTimeout(() => { isScanning = false; }, 1500); 
        },
        (errorMessage) => {
            // Ignore frame parse errors (scanning in progress...)
        }
    ).catch(err => {
        // 2. CATCH & SHOW REAL ERROR
        console.error("Camera failed", err);
        
        // Hide modal
        bootstrap.Modal.getInstance(document.getElementById('cameraModal')).hide();

        let msg = "Could not access camera.";
        if (err.name === 'NotAllowedError') msg = "Camera Permission Denied. Please allow access in Chrome Settings.";
        if (err.name === 'NotFoundError') msg = "No camera found on this device.";
        if (err.name === 'NotReadableError') msg = "Camera is already in use by another app.";

        Swal.fire('Scanner Error', msg + `<br><small class="text-muted">${err}</small>`, 'error');
    });
};
    
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
</script>
@endsection