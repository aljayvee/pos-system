{{-- 
   FILE: resources/views/cashier/index.blade.php 
   FIXES: 
   1. Added missing </script> tag after the cart template (CRITICAL FIX).
   2. Ensured all JavaScript functions are globally accessible.
--}}
@extends('cashier.layout')

@section('content')
{{-- External Libraries --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root { --primary: #4f46e5; --success: #10b981; --bg-light: #f3f4f6; }
    body { background-color: var(--bg-light); font-family: 'Inter', sans-serif; padding-bottom: 80px; /* Space for mobile footer */ }
    
    .product-item {
        cursor: pointer; border: 1px solid #e5e7eb; background: white;
        transition: transform 0.2s, box-shadow 0.2s; border-radius: 12px; overflow: hidden;
    }
    .product-item:active { transform: scale(0.96); }
    .product-item:hover { transform: translateY(-3px); border-color: var(--primary); }
    
    .cart-container {
        background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        height: calc(100vh - 100px); display: flex; flex-direction: column;
    }
    .cart-items-area { flex-grow: 1; overflow-y: auto; padding: 0 10px; }
    
    /* CONNECTION BAR */
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
        .container-fluid { padding-bottom: 80px; }
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
                    
                    {{-- 1. Search + Scan Group (Merged) --}}
                    <div class="input-group flex-grow-1">
                        <span class="input-group-text bg-white border-end-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                        
                        <input type="text" id="product-search" class="form-control border-start-0 border-end-0 py-2" placeholder="Search Item...">
                        
                        {{-- Scan Button (Right Side of Input) --}}
                        <button class="btn btn-dark px-3" onclick="openCameraModal()" title="Scan Barcode">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>

                    {{-- 2. Desktop Buttons (Hidden on Mobile) --}}
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

                {{-- 3. Categories (Compacted for Mobile) --}}
                <div class="d-flex gap-2 mt-2 overflow-auto pb-1 no-scrollbar">
                    <button class="btn btn-dark btn-sm rounded-pill px-3 fw-bold category-filter active" onclick="filterCategory('all', this)">All</button>
                    @foreach($categories as $cat)
                        {{-- Using btn-sm and smaller font size --}}
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
                            data-sku="{{ $product->sku }}"
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

{{-- === MODALS === --}}
@include('cashier.partials.modals')

{{-- === REUSABLE CART UI COMPONENT === --}}
<script id="cart-template" type="text/template">
    @include('cashier.partials.cart-ui')
</script> {{-- <<< THIS WAS THE MISSING TAG CAUSING THE ERROR --}}

<script>
    const CONFIG = {
        pointsValue: Number("{{ \App\Models\Setting::where('key', 'points_conversion')->value('value') ?? 1 }}"),
        loyaltyEnabled: Number("{{ \App\Models\Setting::where('key', 'enable_loyalty')->value('value') ?? 0 }}"),
        paymongoEnabled: Number("{{ \App\Models\Setting::where('key', 'enable_paymongo')->value('value') ?? 0 }}"),
        
        // --- NEW: Pass Tax Settings to JS ---
        birEnabled: Number("{{ $birEnabled ?? 0 }}"), 
        taxType: "{{ \App\Models\Setting::where('key', 'tax_type')->value('value') ?? 'inclusive' }}",

        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };

    // --- 2. STATE MANAGEMENT ---
    let cart = JSON.parse(localStorage.getItem('pos_cart')) || [];
    let currentCustomer = { id: 'walk-in', points: 0, balance: 0 };
    let html5QrcodeScanner = null;
    let isOffline = !navigator.onLine;
    
    const ALL_PRODUCTS = @json($products);

    // --- 3. INITIALIZATION ---
    document.addEventListener('DOMContentLoaded', () => {
        // Render Cart
        const cartHtml = document.getElementById('cart-template').innerHTML;
        document.querySelectorAll('.desktop-cart-col, .offcanvas-body').forEach(el => el.innerHTML = cartHtml);

        // Bind Events
        document.querySelectorAll('#customer-id').forEach(sel => {
            sel.addEventListener('change', function() {
                // Sync all customer dropdowns
                document.querySelectorAll('#customer-id').forEach(s => s.value = this.value);
                const opt = this.options[this.selectedIndex];
                
                currentCustomer = { 
                    id: this.value, 
                    balance: parseFloat(opt.dataset.balance || 0), 
                    points: parseInt(opt.dataset.points || 0) 
                };
                
                if (currentCustomer.balance > 0) {
                     Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: `Customer has debt: ₱${currentCustomer.balance}`, timer: 3000, showConfirmButton: false });
                }
                updateCartUI(); 
            });
        });

        

        

        // C. SEARCH LOGIC (MOVED HERE TO FIX BUG)
        const searchInput = document.getElementById('product-search');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const q = this.value.toLowerCase().trim();
                const cards = document.querySelectorAll('.product-card-wrapper');
                
                cards.forEach(card => {
                    // Safe check for dataset properties
                    const name = (card.dataset.name || '').toLowerCase();
                    const sku = (card.dataset.sku || '').toLowerCase();
                    
                    const match = name.includes(q) || sku.includes(q);
                    card.style.display = match ? 'block' : 'none';
                });
            });
            // Auto-focus on desktop
            if(window.innerWidth > 768) searchInput.focus();
        }

        updateCartUI();
        updateConnectionStatus();
        window.addEventListener('online', () => { updateConnectionStatus(); syncOfflineData(); });
        window.addEventListener('offline', updateConnectionStatus);
    });


    // --- RESTORED: CLEAR CART FUNCTION ---
    window.clearCart = function() {
        if(cart.length === 0) return;
        Swal.fire({
            title: 'Clear Cart?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes'
        }).then((res) => { if(res.isConfirmed) { cart = []; updateCartUI(); } });
    };

    // --- 4. CORE POS LOGIC ---
    // Defined globally so onclick="" attributes can find them
    window.addToCart = function(product) {
        const existing = cart.find(i => i.id === product.id);
        if (existing) {
            if (existing.qty < product.current_stock) {
                existing.qty++;
                playBeep();
            } else {
                playError();
                Swal.fire({ toast: true, icon: 'warning', title: 'Max Stock Reached', position: 'top-end', showConfirmButton: false, timer: 1500 });
                return;
            }
        } else {
            if (product.current_stock > 0) {
                cart.push({ ...product, qty: 1, max: product.current_stock });
                playBeep();
            } else {
                playError();
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

        document.querySelectorAll('#cart-items').forEach(el => el.innerHTML = html);

        // 2. TAX VISIBILITY LOGIC
        let grandTotal = subtotal;
        let taxAmt = 0;
        
        const subtotalEl = document.getElementById('subtotal-display');
        const taxRow = document.getElementById('tax-row'); // The row showing "VAT (12%)"
        const taxEl = document.getElementById('tax-display');

        // LOGIC: Only show if Master Switch is ON (1) AND Type is 'Exclusive'
        if (CONFIG.birEnabled === 1 && CONFIG.taxType === 'exclusive') {
            taxAmt = subtotal * 0.12; 
            grandTotal = subtotal + taxAmt;
            
            // Show the row
            if(taxRow) taxRow.style.setProperty('display', 'flex', 'important');
            if(taxEl) taxEl.innerText = taxAmt.toFixed(2);
        } else {
            // FORCE HIDE if toggle is Off
            if(taxRow) taxRow.style.display = 'none';
        }

        // 3. Update Text
        if(subtotalEl) subtotalEl.innerText = subtotal.toFixed(2);

        document.querySelectorAll('.total-amount-display').forEach(el => el.innerText = grandTotal.toFixed(2));
        if(document.getElementById('mobile-total-display')) document.getElementById('mobile-total-display').innerText = grandTotal.toFixed(2);
        if(document.getElementById('mobile-cart-count')) document.getElementById('mobile-cart-count').innerText = cart.length + ' Items';
        if(document.getElementById('modal-total')) document.getElementById('modal-total').innerText = grandTotal.toFixed(2);
    }

    // --- 5. SEARCH & FILTER ---
    document.getElementById('product-search').addEventListener('keyup', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.product-card-wrapper').forEach(card => {
            const match = card.dataset.name.includes(q) || card.dataset.sku.includes(q);
            card.style.display = match ? 'block' : 'none';
        });
    });

    // --- RESTORED: DEBT FUNCTIONS ---
    // --- 1. REPLACED: Open Debtor List (With Search Data) ---
    window.openDebtorList = function() {
        // Show Modal
        new bootstrap.Modal(document.getElementById('debtorListModal')).show();
        
        // Reset Search Input
        const searchInput = document.getElementById('debtor-search');
        if(searchInput) searchInput.value = '';

        // Show Loading
        const listContainer = document.querySelector('#debtorListModal .list-group');
        listContainer.innerHTML = '<div class="text-center p-4 text-muted"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

        // Fetch Data
        fetch("{{ route('cashier.debtors') }}")
            .then(res => res.json())
            .then(data => {
                listContainer.innerHTML = ''; // Clear loading

                if (data.length === 0) {
                    listContainer.innerHTML = '<div class="text-center p-4 text-muted">No outstanding debts found.</div>';
                    return;
                }

                // Render List
                data.forEach(c => {
                    const btn = document.createElement('button');
                    // Note: We include 'debtor-row' class for the search selector
                    btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center debtor-row';
                    
                    // IMPORTANT: Store lowercase name for easy searching
                    btn.dataset.name = (c.name || '').toLowerCase();
                    
                    btn.onclick = () => openDebtPaymentModal(c.id, c.name, c.balance);
                    
                    btn.innerHTML = `
                        <span class="fw-bold">${c.name}</span>
                        <span class="badge bg-danger rounded-pill">₱${parseFloat(c.balance).toFixed(2)}</span>
                    `;
                    listContainer.appendChild(btn);
                });
            })
            .catch(err => {
                console.error(err);
                listContainer.innerHTML = '<div class="text-center text-danger p-3">Failed to load list.</div>';
            });
    };

    // --- 2. REPLACED: Filter Debtors (Robust Logic) ---
    window.filterDebtors = function() {
        const input = document.getElementById('debtor-search');
        if (!input) return;

        const q = input.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#debtorListModal .debtor-row');

        rows.forEach(row => {
            // Search in the data attribute OR the visible text
            const name = row.dataset.name || row.innerText.toLowerCase();
            
            if (name.includes(q)) {
                // Show item (Restore Flexbox)
                row.classList.remove('d-none');
                row.classList.add('d-flex');
            } else {
                // Hide item
                row.classList.remove('d-flex');
                row.classList.add('d-none');
            }
        });
    };

    // --- 2. REPLACED: Filter Debtors (Robust Logic) ---
    window.filterDebtors = function() {
        const input = document.getElementById('debtor-search');
        if (!input) return;

        const q = input.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#debtorListModal .debtor-row');

        rows.forEach(row => {
            // Search in the data attribute OR the visible text
            const name = row.dataset.name || row.innerText.toLowerCase();
            
            if (name.includes(q)) {
                // Show item (Restore Flexbox)
                row.classList.remove('d-none');
                row.classList.add('d-flex');
            } else {
                // Hide item
                row.classList.remove('d-flex');
                row.classList.add('d-none');
            }
        });
    };

    window.openDebtPaymentModal = function(id, name, balance) {
        // Close list modal first
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

    window.filterCategory = function(cat, btn) {
        document.querySelectorAll('.category-filter').forEach(b => { 
            b.classList.remove('btn-dark'); b.classList.add('btn-light', 'border'); 
        });
        btn.classList.remove('btn-light', 'border'); btn.classList.add('btn-dark');
        
        document.querySelectorAll('.product-card-wrapper').forEach(card => {
            card.style.display = (cat === 'all' || card.dataset.category === cat) ? 'block' : 'none';
        });
    };

    // --- 6. RETURN LOGIC ---
    window.openReturnModal = function() { new bootstrap.Modal(document.getElementById('returnModal')).show(); };

    window.searchSaleForReturn = function() {
        const q = document.getElementById('return-search').value;
        if (!q) return Swal.fire('Error', 'Please enter a Sale ID', 'error');

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
                                        <select class="form-select ret-condition">
                                            <option value="good">Good</option>
                                            <option value="damaged">Damaged</option>
                                        </select>
                                    </td>
                                </tr>`;
                        }
                    });
                } else {
                    Swal.fire('Not Found', data.message, 'error');
                }
            })
            .catch(err => Swal.fire('Error', 'Sale not found', 'error'));
    };

    window.calcRefund = function() {
        let total = 0;
        document.querySelectorAll('#return-items-body tr').forEach(row => {
            const price = parseFloat(row.getAttribute('data-price'));
            const qty = parseInt(row.querySelector('.ret-qty').value) || 0;
            total += price * qty;
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

    // --- 7. CAMERA ---
    window.openCameraModal = function() { new bootstrap.Modal(document.getElementById('cameraModal')).show(); if (!html5QrcodeScanner) { html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 }, false); html5QrcodeScanner.render((txt) => { const prod = ALL_PRODUCTS.find(p => p.sku === txt); if(prod) { addToCart(prod); Swal.fire({toast:true, position:'top', icon:'success', title:'Added', timer:1000, showConfirmButton:false}); }}); }};
    window.stopCamera = function() { if(html5QrcodeScanner) html5QrcodeScanner.clear(); };

    window.openPaymentModal = function() {
        if(cart.length === 0) return Swal.fire('Empty', 'Add items first', 'warning');
        
        // 1. Reset Inputs
        document.getElementById('amount-paid').value = '';
        document.getElementById('change-display').innerText = '₱0.00';
        
        // 2. Get Elements
        const cashRadio = document.getElementById('pm-cash');
        const digitalRadio = document.getElementById('pm-digital');
        const creditRadio = document.getElementById('pm-credit');
        
        const cashLbl = cashRadio.nextElementSibling;
        const digiLbl = digitalRadio.nextElementSibling;
        const credLbl = creditRadio.nextElementSibling;

        // 3. Reset All to Active First
        cashRadio.disabled = false; cashLbl.classList.remove('opacity-50');
        digitalRadio.disabled = false; digiLbl.classList.remove('opacity-50');
        creditRadio.disabled = false; credLbl.classList.remove('opacity-50');

        // 4. Apply Strict Logic
        if (currentCustomer.id === 'new') {
            // CASE A: NEW CREDIT CUSTOMER -> FORCE CREDIT ONLY
            cashRadio.disabled = true; cashLbl.classList.add('opacity-50');
            digitalRadio.disabled = true; digiLbl.classList.add('opacity-50');
            
            creditRadio.checked = true; // Auto-check Credit
        } 
        else if (currentCustomer.id === 'walk-in') {
            // CASE B: WALK-IN -> NO CREDIT ALLOWED
            creditRadio.disabled = true; credLbl.classList.add('opacity-50');
            
            cashRadio.checked = true; // Auto-check Cash
        } 
        else {
            // CASE C: EXISTING CUSTOMER -> ALLOW ALL (Default to Cash)
            if(!creditRadio.checked && !digitalRadio.checked) cashRadio.checked = true;
        }

        toggleFlow();
        new bootstrap.Modal(document.getElementById('paymentModal')).show();
        
        // Focus Logic
        setTimeout(() => {
            if(!cashRadio.disabled && cashRadio.checked) document.getElementById('amount-paid').focus();
            if(creditRadio.checked) document.getElementById('credit-name').focus();
        }, 500);
    };

    window.toggleFlow = function() {
        const method = document.querySelector('input[name="paymethod"]:checked').value;
        document.getElementById('flow-cash').style.display = method === 'cash' ? 'block' : 'none';
        document.getElementById('flow-digital').style.display = method === 'digital' ? 'block' : 'none';
        document.getElementById('flow-credit').style.display = method === 'credit' ? 'block' : 'none';
    };

    window.calculateChange = function() {
        const total = parseFloat(document.getElementById('modal-total').innerText.replace(',',''));
        const paid = parseFloat(document.getElementById('amount-paid').value) || 0;
        const change = paid - total;
        const disp = document.getElementById('change-display');
        disp.innerText = change >= 0 ? '₱' + change.toFixed(2) : 'Invalid';
        disp.className = change >= 0 ? 'fw-bold text-success fs-5' : 'fw-bold text-danger fs-5';
    };

    window.processPayment = function() {
        const method = document.querySelector('input[name="paymethod"]:checked').value;
        const total = parseFloat(document.getElementById('modal-total').innerText.replace(',',''));
        
        if (method === 'cash') {
            const paid = parseFloat(document.getElementById('amount-paid').value) || 0;
            if (paid < total) return Swal.fire('Error', 'Insufficient Cash Payment', 'error');
        } 

        // --- CAPTURE DETAILS ---
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

        // Offline Check
        if (isOffline) { saveToOfflineQueue(payload); return; }

        Swal.showLoading();
        
        // --- REQUEST WITH FIXED HEADERS ---
        fetch("{{ route('cashier.store') }}", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json", 
                "Accept": "application/json", // <--- CRITICAL FIX
                "X-CSRF-TOKEN": CONFIG.csrfToken 
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const data = await res.json();
            // Handle Validation Errors (422) or Server Errors (500)
            if (!res.ok) {
                throw new Error(data.message || 'Server Error');
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                Swal.fire({
                    icon: 'success', title: 'Paid!', showCancelButton: true, confirmButtonText: 'Receipt', cancelButtonText: 'New Sale'
                }).then((r) => {
                    cart = [];
                    localStorage.removeItem('pos_cart');
                    updateCartUI(); 
                    document.getElementById('customer-id').value = 'walk-in'; 
                    currentCustomer = { id: 'walk-in', points: 0, balance: 0 };

                    if (r.isConfirmed) window.open(`/cashier/receipt/${data.sale_id}`, '_blank', 'width=400,height=600');
                });
            } else {
                throw new Error(data.message);
            }
        })
        .catch(err => {
            console.error(err);
            // Only save offline if it's actually a NETWORK error, not a Validation error
            if (err.message.toLowerCase().includes('fetch') || err.message.toLowerCase().includes('network')) {
                saveToOfflineQueue(payload);
            } else {
                // Show the specific validation error (e.g., "Full Name is required")
                Swal.fire('Validation Error', err.message, 'warning');
            }
        });
    };

    function saveToOfflineQueue(data) {
        let queue = JSON.parse(localStorage.getItem('offline_queue_sales')) || [];
        data.offline_id = Date.now(); queue.push(data);
        localStorage.setItem('offline_queue_sales', JSON.stringify(queue));
        
        // Clear Cart Offline
        cart = [];
        localStorage.removeItem('pos_cart');
        updateCartUI();
        bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
        
        Swal.fire('Saved Offline', 'Transaction stored locally.', 'info');
    }

    function updateConnectionStatus() {
       isOffline = !navigator.onLine;
        document.getElementById('connection-status').className = isOffline ? 'status-offline' : 'status-online';
    }

    function syncOfflineData() { if(!isOffline && localStorage.getItem('offline_queue_sales')) Swal.fire({toast:true, title:'Syncing...', position:'top-end', timer:2000, showConfirmButton:false}); }

    function searchSaleForReturn() { /* Return logic */ }
    function submitReturn() { /* Return logic */ }
    function playBeep() { /* Sound */ }
    function playError() { /* Sound */ }
</script>
@endsection