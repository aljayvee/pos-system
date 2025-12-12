@extends('cashier.layout')

@section('content')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<div class="container-fluid p-3">
    <div class="row g-3">
        
        <div class="col-md-7">
            {{-- Connection Status --}}
            <div id="offline-alert" class="alert alert-warning mb-3 shadow-sm" style="display: none;">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-wifi-slash me-2"></i> <strong>Offline Mode.</strong> Sales saved locally.</span>
                    <span class="badge bg-dark" id="pending-count">0 Pending</span>
                </div>
            </div>

            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex gap-2">
                        {{-- Search Input --}}
                        <div class="input-group flex-grow-1">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search"></i></span>
                            <input type="text" id="product-search" class="form-control border-start-0 bg-light" 
                                   placeholder="Scan Barcode or Search Item..." autofocus autocomplete="off">
                        </div>
                        
                        {{-- Scan Button --}}
                        <button class="btn btn-outline-dark" onclick="openCameraModal()">
                            <i class="fas fa-camera"></i> <span class="d-none d-md-inline">Scan</span>
                        </button>

                        {{-- NEW: DEBT COLLECTION BUTTON --}}
                        <button class="btn btn-danger fw-bold" onclick="openDebtorList()">
                            <i class="fas fa-hand-holding-usd"></i> Pay Debt
                        </button>
                    </div>
                </div>

                {{-- Category Filter --}}
                <div class="px-3 py-2 bg-light border-bottom overflow-auto text-nowrap" style="white-space: nowrap;">
                    <button class="btn btn-dark rounded-pill me-1 category-filter active" onclick="filterCategory('all', this)">All</button>
                    @foreach($categories as $cat)
                        <button class="btn btn-outline-secondary rounded-pill me-1 category-filter" 
                                onclick="filterCategory('{{ strtolower($cat->name) }}', this)">{{ $cat->name }}</button>
                    @endforeach
                </div>

                {{-- Product Grid --}}
                <div class="card-body p-0 overflow-auto" style="height: 70vh;">
                    <div class="row g-2 p-3" id="product-list">
                        @foreach($products as $product)
                        <div class="col-md-4 col-sm-6 product-card" 
                             data-name="{{ strtolower($product->name) }}" 
                             data-sku="{{ $product->sku }}"
                             data-category="{{ strtolower($product->category->name ?? '') }}">
                            <div class="card h-100 shadow-sm border-0 product-item" onclick='addToCart(@json($product))' style="cursor: pointer;">
                                <div class="card-body text-center p-2">
                                    <h6 class="fw-bold text-dark mb-1 text-truncate">{{ $product->name }}</h6>
                                    <span class="badge bg-light text-dark border mb-1">{{ ucfirst($product->unit) }}</span>
                                    <h5 class="text-primary fw-bold mb-0">₱{{ number_format($product->price, 2) }}</h5>
                                    @if($product->stock <= 5)
                                        <small class="text-danger fw-bold d-block">Low Stock: {{ $product->stock }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div id="no-products" class="text-center mt-5" style="display: none;">
                        <h4 class="text-muted">Item not found</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm h-100 border-0 d-flex flex-column">
                <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0"><i class="fas fa-shopping-cart me-2"></i> Current Order</h5>
                    
                    {{-- MOVED: Clear Button --}}
                    <button class="btn btn-sm btn-light text-danger fw-bold" onclick="clearCart()">
                        <i class="fas fa-trash-alt me-1"></i> Clear
                    </button>
                </div>
                
                <div class="card-body p-0 overflow-auto flex-grow-1" style="max-height: 40vh;">
                    <ul class="list-group list-group-flush" id="cart-items">
                        <li class="list-group-item text-center text-muted mt-5 border-0">Cart is empty</li>
                    </ul>
                </div>

                <div class="card-footer bg-light p-3 border-top">
                    
                    {{-- Points Redemption --}}
                    <div id="redemption-section" class="mb-3 p-2 border rounded bg-white" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-warning"><i class="fas fa-star"></i> Use Points?</span>
                            <small class="text-muted">Available: <span id="avail-points">0</span></small>
                        </div>
                        <div class="input-group input-group-sm">
                            <input type="number" id="points-to-use" class="form-control" placeholder="0" min="0" oninput="calculateTotalWithPoints()">
                            <span class="input-group-text text-success">-₱<span id="discount-display">0.00</span></span>
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="d-flex justify-content-between mb-3">
                        <h4 class="fw-bold">Total</h4>
                        <h4 class="fw-bold text-primary">₱<span id="total-amount">0.00</span></h4>
                    </div>

                    {{-- Customer Selection --}}
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">CUSTOMER TYPE</label>
                        <select id="customer-id" class="form-select form-select-lg">
                            <option value="walk-in" data-points="0" selected>Walk-in Customer</option>
                            <option value="new" data-points="0">+ New Customer (Credit/Utang)</option>
                            <optgroup label="Existing Customers">
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                            data-balance="{{ $customer->balance ?? 0 }}"
                                            data-points="{{ $customer->points }}">
                                        {{ $customer->name }} 
                                        @if($customer->balance > 0) (Debt: ₱{{ number_format($customer->balance) }}) @endif
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    {{-- Payment Method --}}
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">PAYMENT METHOD</label>
                        <select id="payment-method" class="form-select form-select-lg" onchange="toggleFlow()">
                            <option value="cash">Cash Payment</option>
                            <option value="digital">Digital Wallet (Gcash/Maya)</option>
                            <option value="credit" id="opt-credit" disabled>Credit (Utang)</option>
                        </select>
                    </div>

                    {{-- Inputs --}}
                    <div id="flow-cash" class="mb-3">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">₱</span>
                            <input type="number" id="amount-paid" class="form-control" placeholder="Amount Received" oninput="calculateChange()">
                        </div>
                        <small class="text-success fw-bold d-block mt-1">Change: <span id="change-display">₱0.00</span></small>
                    </div>

                    <div id="flow-digital" class="mb-3" style="display: none;">
                        <input type="text" id="reference-number" class="form-control form-control-lg" placeholder="Reference No.">
                    </div>

                    <div id="flow-credit" style="display: none;">
                        <div class="alert alert-info py-2 small"><i class="fas fa-info-circle"></i> Enter customer details in popup.</div>
                    </div>

                    {{-- Pay Button --}}
                    <button class="btn btn-success w-100 btn-lg fw-bold" onclick="handlePayNow()">
                        <i class="fas fa-check-circle me-2"></i> PAY NOW
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="debtorListModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-users me-2"></i> Customers with Debt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="debtor-search" class="form-control mb-3" placeholder="Search customer name..." onkeyup="filterDebtors()">
                
                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th class="text-end">Outstanding Balance</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="debtor-table-body">
                            @foreach($customers as $c)
                                @if($c->balance > 0)
                                <tr class="debtor-row" data-name="{{ strtolower($c->name) }}">
                                    <td class="fw-bold">{{ $c->name }}</td>
                                    <td class="text-end text-danger fw-bold">₱{{ number_format($c->balance, 2) }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="openDebtPaymentModal('{{ $c->id }}', '{{ $c->name }}', '{{ $c->balance }}')">
                                            Select
                                        </button>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="debtPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Collect Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pay-debt-customer-id">
                
                <div class="text-center mb-4">
                    <h5 id="pay-debt-name" class="fw-bold"></h5>
                    <small class="text-muted">Total Debt</small>
                    <h3 class="text-danger fw-bold">₱<span id="pay-debt-balance">0.00</span></h3>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Payment Amount</label>
                    <input type="number" id="pay-debt-amount" class="form-control form-control-lg" placeholder="0.00" oninput="calcDebtChange()">
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Cash Tendered (Optional)</label>
                    <input type="number" id="pay-debt-tendered" class="form-control" placeholder="0.00" oninput="calcDebtChange()">
                    <div class="form-text text-end fw-bold text-success">Change: ₱<span id="pay-debt-change">0.00</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger fw-bold" onclick="processDebtPayment()">Confirm Payment</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="creditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">New Credit Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="credit-name" class="form-control mb-2" placeholder="Customer Name">
                <input type="text" id="credit-contact" class="form-control mb-2" placeholder="Contact Number">
                <textarea id="credit-address" class="form-control mb-2" placeholder="Address"></textarea>
                <label class="small text-muted">Promise to Pay Date:</label>
                <input type="date" id="credit-due-date" class="form-control">
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary w-100" onclick="confirmTransaction('credit')">Confirm Credit</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cameraModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Scan Barcode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="stopCamera()"></button>
            </div>
            <div class="modal-body text-center">
                <div id="reader" style="width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .product-item:hover { transform: translateY(-3px); background-color: #f8f9fa; }
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; }
    ::-webkit-scrollbar-thumb { background: #888; border-radius: 3px; }
</style>

<script>
    const pointsValue = {{ \App\Models\Setting::where('key', 'points_conversion')->value('value') ?? 1 }};
    const loyaltyEnabled = {{ \App\Models\Setting::where('key', 'enable_loyalty')->value('value') ?? 0 }};
    let cart = JSON.parse(localStorage.getItem('pos_cart')) || [];
    let html5QrcodeScanner = null;
    let currentCustomerPoints = 0; 

    window.onload = () => {
        document.getElementById('product-search').focus();
        if(cart.length > 0) updateCartUI();
        updateOnlineStatus();
        toggleFlow(); // Set initial state
    };

    // --- LOGIC: CUSTOMER TYPE & PAYMENT METHOD ---
    document.getElementById('customer-id').addEventListener('change', function() {
        const type = this.value;
        const paySelect = document.getElementById('payment-method');
        const creditOpt = document.getElementById('opt-credit');
        const selectedOption = this.options[this.selectedIndex];

        // STEP 1: Reset all options to enabled first (to avoid getting stuck)
        Array.from(paySelect.options).forEach(opt => opt.disabled = false);

        // STEP 2: Apply Logic based on Type
        if (type === 'new') {
            // Logic for "New Customer" -> MUST use Credit
            creditOpt.disabled = false; // 1. Enable Credit First
            paySelect.value = 'credit'; // 2. Select Credit
            
            // 3. Disable Cash & Digital
            Array.from(paySelect.options).forEach(opt => {
                if (opt.value !== 'credit') opt.disabled = true;
            });
        } 
        else if (type === 'walk-in') {
            // Logic for "Walk-in" -> NO Credit allowed
            creditOpt.disabled = true; // Disable Credit
            
            // If currently on Credit, switch to Cash automatically
            if (paySelect.value === 'credit') {
                paySelect.value = 'cash';
            }
        }
        else {
            // Logic for Existing Customers -> All methods allowed
            // (Already reset to enabled in Step 1)
        }

        // STEP 3: Update Loyalty Display (Existing Logic)
        currentCustomerPoints = parseInt(selectedOption.getAttribute('data-points') || 0);
        const badge = document.getElementById('redemption-section');
        
        if (loyaltyEnabled == 1 && type !== 'walk-in' && type !== 'new') {
            if(document.getElementById('avail-points')) {
                document.getElementById('avail-points').innerText = currentCustomerPoints;
            }
            if(badge) badge.style.display = 'block';
        } else {
            if(badge) badge.style.display = 'none';
        }

        // STEP 4: Update UI Inputs
        toggleFlow();
    });

    function toggleFlow() {
        const method = document.getElementById('payment-method').value;
        ['cash', 'digital', 'credit'].forEach(m => {
            const el = document.getElementById('flow-'+m);
            if(el) el.style.display = (method === m) ? 'block' : 'none';
        });
    }

    // --- LOGIC: DEBT COLLECTION ---
    function openDebtorList() {
        new bootstrap.Modal(document.getElementById('debtorListModal')).show();
    }

    function filterDebtors() {
        const query = document.getElementById('debtor-search').value.toLowerCase();
        document.querySelectorAll('.debtor-row').forEach(row => {
            const name = row.getAttribute('data-name');
            row.style.display = name.includes(query) ? '' : 'none';
        });
    }

    function openDebtPaymentModal(id, name, balance) {
        // 1. Get the list modal instance
        const listModalEl = document.getElementById('debtorListModal');
        const listModal = bootstrap.Modal.getInstance(listModalEl);
        
        // 2. Hide it first
        listModal.hide();
        
        // 3. Wait for it to fully close before opening the payment modal
        // This prevents the "aria-hidden" focus error
        listModalEl.addEventListener('hidden.bs.modal', function () {
            document.getElementById('pay-debt-customer-id').value = id;
            document.getElementById('pay-debt-name').innerText = name;
            document.getElementById('pay-debt-balance').innerText = parseFloat(balance).toFixed(2);
            document.getElementById('pay-debt-amount').value = '';
            document.getElementById('pay-debt-tendered').value = '';
            document.getElementById('pay-debt-change').innerText = '0.00';
            
            new bootstrap.Modal(document.getElementById('debtPaymentModal')).show();
        }, { once: true });
    }

    function calcDebtChange() {
        const amount = parseFloat(document.getElementById('pay-debt-amount').value) || 0;
        const tendered = parseFloat(document.getElementById('pay-debt-tendered').value) || 0;
        const change = tendered - amount;
        document.getElementById('pay-debt-change').innerText = change >= 0 ? change.toFixed(2) : '0.00';
    }

    function processDebtPayment() {
        const id = document.getElementById('pay-debt-customer-id').value;
        const amount = document.getElementById('pay-debt-amount').value;

        if(!amount || amount <= 0) { alert("Enter valid amount"); return; }

        fetch("{{ route('cashier.credit.pay') }}", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json", 
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
            },
            body: JSON.stringify({ customer_id: id, amount: amount })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert("Payment Collected!");
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => alert("Error: " + err));
    }

    // --- STANDARD POS FUNCTIONS (Cart, Scan, Pay) ---
    function addToCart(product) {
        const existingItem = cart.find(item => item.id === product.id);
        if (existingItem) {
            if(existingItem.qty < product.stock) existingItem.qty++;
            else { alert('Not enough stock!'); return; }
        } else {
            if(product.stock > 0) {
                cart.push({ id: product.id, name: product.name, price: parseFloat(product.price), qty: 1, max_stock: product.stock, unit: product.unit });
            } else { alert('Out of stock!'); return; }
        }
        updateCartUI();
    }

    function updateQty(id, change) {
        const item = cart.find(i => i.id === id);
        if (!item) return;
        item.qty += change;
        if (item.qty <= 0) cart = cart.filter(i => i.id !== id);
        else if (item.qty > item.max_stock) { item.qty = item.max_stock; alert("Max stock reached"); }
        updateCartUI();
    }

    function updateCartUI() {
        localStorage.setItem('pos_cart', JSON.stringify(cart));
        const list = document.getElementById('cart-items');
        list.innerHTML = '';
        if (cart.length === 0) list.innerHTML = '<li class="list-group-item text-center text-muted mt-5 border-0">Cart is empty</li>';

        cart.forEach(item => {
            list.innerHTML += `
                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                    <div>
                        <h6 class="m-0 fw-bold">${item.name}</h6>
                        <small class="text-muted">${item.unit ? '('+item.unit+')' : ''} ₱${item.price} x ${item.qty}</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="text-success fw-bold me-3">₱${(item.price * item.qty).toFixed(2)}</span>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary" onclick="updateQty(${item.id}, -1)">-</button>
                            <button class="btn btn-outline-secondary" onclick="updateQty(${item.id}, 1)">+</button>
                        </div>
                    </div>
                </li>`;
        });
        calculateTotalWithPoints();
    }

    function clearCart() {
        if(confirm("Clear cart?")) {
            cart = [];
            updateCartUI();
        }
    }

    function calculateTotalWithPoints() {
        let subtotal = 0;
        cart.forEach(item => subtotal += item.price * item.qty);
        
        // Loyalty Logic
        const pointsInputEl = document.getElementById('points-to-use');
        let pointsInput = 0;
        if (pointsInputEl) {
            pointsInput = parseInt(pointsInputEl.value) || 0;
            if(pointsInput > currentCustomerPoints) pointsInput = currentCustomerPoints;
            let discount = pointsInput * pointsValue;
            if(discount > subtotal) discount = subtotal;
            document.getElementById('discount-display').innerText = discount.toFixed(2);
            subtotal -= discount;
        }

        document.getElementById('total-amount').innerText = subtotal.toFixed(2);
        calculateChange();
    }

    function calculateChange() {
        const total = parseFloat(document.getElementById('total-amount').innerText.replace(/,/g, '')) || 0;
        const paid = parseFloat(document.getElementById('amount-paid').value) || 0;
        const change = paid - total;
        document.getElementById('change-display').innerText = change >= 0 ? '₱' + change.toFixed(2) : 'Invalid';
    }

    function handlePayNow() {
        if (cart.length === 0) { alert("Cart is empty!"); return; }
        const method = document.getElementById('payment-method').value;

        if (method === 'credit') {
            const custType = document.getElementById('customer-id').value;
            // Pre-fill modal if new customer
            if (custType === 'new') {
                document.getElementById('credit-name').value = ''; 
                document.getElementById('credit-name').disabled = false;
            } else {
                // If existing customer selected, maybe auto-fill name?
                // For now, let's allow manual entry or keep it simple.
            }
            new bootstrap.Modal(document.getElementById('creditModal')).show();
        } else if (confirm("Process Payment?")) {
            confirmTransaction(method);
        }
    }

    function confirmTransaction(method) {
        let creditData = {};
        const customerVal = document.getElementById('customer-id').value;

        if (method === 'credit') {
             const name = document.getElementById('credit-name').value;
             const dueDate = document.getElementById('credit-due-date').value;
             if(!name || !dueDate) { alert("Name & Due Date required for credit"); return; }
             
             creditData = { 
                 name: name, 
                 address: document.getElementById('credit-address').value, 
                 contact: document.getElementById('credit-contact').value, 
                 due_date: dueDate 
             };
        }

        const data = {
            cart: cart,
            total_amount: document.getElementById('total-amount').innerText.replace(/,/g, ''),
            points_used: document.getElementById('points-to-use') ? document.getElementById('points-to-use').value : 0,
            amount_paid: method === 'cash' ? document.getElementById('amount-paid').value : 0,
            payment_method: method,
            customer_id: customerVal,
            reference_number: document.getElementById('reference-number').value,
            credit_details: creditData
        };

        if (!navigator.onLine) {
            saveOffline(data);
            return;
        }

        fetch("{{ route('cashier.store') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                localStorage.removeItem('pos_cart');
                cart = [];
                if (confirm("Success! Print Receipt?")) window.open(`/cashier/receipt/${data.sale_id}`, '_blank', 'width=400,height=600');
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        });
    }

    // --- OFFLINE SYNC ---
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    function updateOnlineStatus() {
        const queue = JSON.parse(localStorage.getItem('offline_sales')) || [];
        const alertBox = document.getElementById('offline-alert');
        if (!navigator.onLine) {
            alertBox.style.display = 'block';
            document.getElementById('pending-count').innerText = queue.length + ' Pending';
        } else if (queue.length > 0) {
            alertBox.style.display = 'block';
            alertBox.classList.replace('alert-warning', 'alert-info');
            alertBox.innerHTML = `<span>Online! Syncing ${queue.length} sales...</span> <button class="btn btn-sm btn-light" onclick="syncOfflineSales()">Sync</button>`;
            syncOfflineSales();
        } else {
            alertBox.style.display = 'none';
        }
    }
    
    // ... (Keep existing syncOfflineSales and saveOffline functions) ...
    // Note: Re-paste them if you removed them, they are crucial.
    
    function saveOffline(data) {
        let queue = JSON.parse(localStorage.getItem('offline_sales')) || [];
        queue.push(data);
        localStorage.setItem('offline_sales', JSON.stringify(queue));
        localStorage.removeItem('pos_cart');
        alert("Saved Offline!");
        location.reload();
    }

    async function syncOfflineSales() {
        let queue = JSON.parse(localStorage.getItem('offline_sales')) || [];
        if (queue.length === 0) return;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        for (let i = 0; i < queue.length; i++) {
            await fetch("{{ route('cashier.store') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                body: JSON.stringify(queue[i])
            });
        }
        localStorage.removeItem('offline_sales');
        alert("Sync Complete!");
        location.reload();
    }

    // --- BARCODE CAMERA ---
    function openCameraModal() {
        const modal = new bootstrap.Modal(document.getElementById('cameraModal'));
        modal.show();
        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 }, false);
            html5QrcodeScanner.render((txt) => {
                const prod = @json($products).find(p => p.sku === txt);
                if(prod) { addToCart(prod); alert("Added "+prod.name); }
            });
        }
    }
    function stopCamera() { if(html5QrcodeScanner) html5QrcodeScanner.clear(); }

    // --- SEARCH FILTER ---
    document.getElementById('product-search').addEventListener('keyup', function(e) {
        const val = this.value.toLowerCase();
        document.querySelectorAll('.product-card').forEach(card => {
            const name = card.getAttribute('data-name');
            const sku = card.getAttribute('data-sku');
            card.style.display = (name.includes(val) || sku.includes(val)) ? 'block' : 'none';
        });
    });

    function filterCategory(cat, btn) {
        document.querySelectorAll('.category-filter').forEach(b => { b.classList.remove('btn-dark','active'); b.classList.add('btn-outline-secondary'); });
        btn.classList.remove('btn-outline-secondary'); btn.classList.add('btn-dark','active');
        document.querySelectorAll('.product-card').forEach(card => {
            card.style.display = (cat === 'all' || card.getAttribute('data-category') === cat) ? 'block' : 'none';
        });
    }
</script>
@endsection