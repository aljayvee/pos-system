@extends('cashier.layout')

@section('content')
{{-- Import QR Code Library --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<div class="container-fluid p-3">
    <div class="row g-3">
        
        <div class="col-md-7">
            <div id="offline-alert" class="alert alert-warning mb-3 shadow-sm" style="display: none;">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-wifi-slash me-2"></i> <strong>Offline Mode.</strong> Sales saved locally.</span>
                    <span class="badge bg-dark" id="pending-count">0 Pending</span>
                </div>
            </div>

            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex gap-2">
                        <div class="input-group flex-grow-1">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search"></i></span>
                            <input type="text" id="product-search" class="form-control border-start-0 bg-light" 
                                   placeholder="Scan Barcode or Search Item..." autofocus autocomplete="off">
                        </div>
                        <button class="btn btn-outline-dark" onclick="openCameraModal()">
                            <i class="fas fa-camera"></i> <span class="d-none d-md-inline">Scan</span>
                        </button>
                        <button class="btn btn-danger fw-bold" onclick="openDebtorList()">
                            <i class="fas fa-hand-holding-usd"></i> Pay Debt
                        </button>
                    </div>
                </div>

                <div class="px-3 py-2 bg-light border-bottom overflow-auto text-nowrap" style="white-space: nowrap;">
                    <button class="btn btn-dark rounded-pill me-1 category-filter active" onclick="filterCategory('all', this)">All</button>
                    @foreach($categories as $cat)
                        <button class="btn btn-outline-secondary rounded-pill me-1 category-filter" 
                                onclick="filterCategory('{{ strtolower($cat->name) }}', this)">{{ $cat->name }}</button>
                    @endforeach
                </div>

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

                    <div class="d-flex justify-content-between mb-3">
                        <h4 class="fw-bold">Total</h4>
                        <h4 class="fw-bold text-primary">₱<span id="total-amount">0.00</span></h4>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted">CUSTOMER TYPE</label>
                        <select id="customer-id" class="form-select form-select-lg">
                            <option value="walk-in" data-points="0">Walk-in Customer</option>
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
                        {{-- DEBT INFO --}}
                        <div id="debt-section" class="mt-2 p-2 bg-danger bg-opacity-10 border border-danger rounded d-flex justify-content-between align-items-center" style="display: none;">
                            <div>
                                <small class="text-danger fw-bold d-block">Outstanding Balance</small>
                                <span class="fs-5 fw-bold text-danger">₱<span id="customer-balance">0.00</span></span>
                            </div>
                            <button class="btn btn-sm btn-danger" onclick="openDebtModal()">
                                <i class="fas fa-hand-holding-usd"></i> Pay Debt
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted">PAYMENT METHOD</label>
                        <select id="payment-method" class="form-select form-select-lg" onchange="toggleFlow()">
                            <option value="cash">Cash Payment</option>
                            <option value="digital">Digital Wallet (Gcash/Maya)</option>
                            <option value="credit" id="opt-credit" disabled>Credit (Utang)</option>
                        </select>
                    </div>

                    {{-- CASH --}}
                    <div id="flow-cash" class="mb-3">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">₱</span>
                            <input type="number" id="amount-paid" class="form-control" placeholder="Amount Received" oninput="calculateChange()">
                        </div>
                        <small class="text-success fw-bold d-block mt-1">Change: <span id="change-display">₱0.00</span></small>
                    </div>

                    {{-- DIGITAL (PAYMONGO) --}}
                    <div id="flow-digital" class="mb-3" style="display: none;">
                        @if(\App\Models\Setting::where('key', 'enable_paymongo')->value('value') == '1')
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" onclick="generatePaymentLink()">
                                    <i class="fas fa-qrcode me-2"></i> Generate GCash/Maya QR
                                </button>
                                <div id="digital-status" class="text-center small text-muted">Click to generate payment link</div>
                            </div>
                            <input type="hidden" id="paymongo-id">
                            <input type="text" id="reference-number" class="form-control mt-2 text-center" 
                                   placeholder="Reference # (Auto-filled)" readonly>
                        @else
                            <label class="small text-muted">Reference Number</label>
                            <input type="text" id="reference-number" class="form-control form-control-lg" placeholder="Enter Ref No.">
                        @endif
                    </div>

                    {{-- CREDIT --}}
                    <div id="flow-credit" style="display: none;">
                        <div class="alert alert-info py-2 small"><i class="fas fa-info-circle"></i> Enter customer details in popup.</div>
                    </div>

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
                <h5 class="modal-title">Customers with Debt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="debtor-search" class="form-control mb-3" placeholder="Search..." onkeyup="filterDebtors()">
                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table table-hover align-middle">
                        <thead class="table-light"><tr><th>Name</th><th class="text-end">Balance</th><th>Action</th></tr></thead>
                        <tbody>
                            @foreach($customers as $c)
                                @if($c->balance > 0)
                                <tr class="debtor-row" data-name="{{ strtolower($c->name) }}">
                                    <td class="fw-bold">{{ $c->name }}</td>
                                    <td class="text-end text-danger">₱{{ number_format($c->balance, 2) }}</td>
                                    <td><button class="btn btn-sm btn-outline-danger" onclick="openDebtPaymentModal('{{ $c->id }}', '{{ $c->name }}', '{{ $c->balance }}')">Select</button></td>
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
                    <h3 class="text-danger fw-bold">₱<span id="pay-debt-balance">0.00</span></h3>
                </div>
                <div class="mb-3">
                    <label>Payment Amount</label>
                    <input type="number" id="pay-debt-amount" class="form-control form-control-lg" oninput="calcDebtChange()">
                </div>
                <div class="mb-3">
                    <label class="small text-muted">Cash Tendered</label>
                    <input type="number" id="pay-debt-tendered" class="form-control" oninput="calcDebtChange()">
                    <div class="text-end text-success fw-bold">Change: ₱<span id="pay-debt-change">0.00</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" onclick="processDebtPayment()">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="creditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Credit Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="credit-name" class="form-control mb-2" placeholder="Customer Name">
                <input type="text" id="credit-contact" class="form-control mb-2" placeholder="Contact Number">
                <textarea id="credit-address" class="form-control mb-2" placeholder="Address"></textarea>
                <label class="small text-muted">Due Date:</label>
                <input type="date" id="credit-due-date" class="form-control">
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary w-100" onclick="confirmTransaction('credit')">Confirm Credit</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="qrPaymentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Scan to Pay</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qrcode" class="mb-3 d-flex justify-content-center"></div>
                <h4 class="fw-bold text-primary">₱<span id="qr-amount">0.00</span></h4>
                <div id="payment-status" class="fw-bold text-warning">Waiting for payment...</div>
                <div id="payment-spinner" class="spinner-border text-primary mt-2" role="status"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cameraModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Scan Barcode</h5><button class="btn-close" data-bs-dismiss="modal" onclick="stopCamera()"></button></div>
            <div class="modal-body"><div id="reader" style="width: 100%;"></div></div>
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
    let paymentCheckInterval = null; // Important global variable

    window.onload = () => {
        if(document.getElementById('product-search')) document.getElementById('product-search').focus();
        if(cart.length > 0) updateCartUI();
        updateOnlineStatus();
        toggleFlow();
    };

    // --- PAYMONGO LOGIC ---
    function generatePaymentLink() {
        const amountStr = document.getElementById('total-amount').innerText.replace(/,/g, '');
        const amount = parseFloat(amountStr);

        if(amount < 100) {
            alert("Minimum amount for online payment is ₱100.00. Please add more items.");
            return;
        }

        const btn = document.querySelector('#flow-digital button');
        const statusDiv = document.getElementById('digital-status');
        const origText = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connecting...';
        btn.disabled = true;
        if(statusDiv) statusDiv.innerText = "Contacting PayMongo...";

        fetch("{{ route('payment.create') }}", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json", 
                "Accept": "application/json", 
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
            },
            body: JSON.stringify({ amount: amount })
        })
        .then(async res => {
            if (!res.ok) {
                const text = await res.text();
                try {
                    const jsonErr = JSON.parse(text);
                    if(jsonErr.errors && jsonErr.errors[0] && jsonErr.errors[0].detail) throw new Error(jsonErr.errors[0].detail);
                } catch(e) {}
                throw new Error(`API Error (${res.status})`); 
            }
            return res.json();
        })
        .then(data => {
            btn.innerHTML = origText;
            btn.disabled = false;
            if(statusDiv) statusDiv.innerText = "Click to generate payment link";

            if(data.success) {
                const qrAmt = document.getElementById('qr-amount');
                const qrDiv = document.getElementById('qrcode');
                
                if(qrAmt) qrAmt.innerText = amountStr;
                if(qrDiv) {
                    qrDiv.innerHTML = "";
                    new QRCode(qrDiv, { text: data.checkout_url, width: 180, height: 180 });
                }

                new bootstrap.Modal(document.getElementById('qrPaymentModal')).show();
                
                document.getElementById('paymongo-id').value = data.id;
                document.getElementById('reference-number').value = data.reference_number;
                
                startPolling(data.id);
            } else {
                alert("Payment Error: " + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            btn.innerHTML = origText;
            btn.disabled = false;
            if(statusDiv) statusDiv.innerText = "Connection Failed";
            alert(err.message);
        });
    }

    function startPolling(id) {
        if(paymentCheckInterval) clearInterval(paymentCheckInterval);
        
        paymentCheckInterval = setInterval(() => {
            fetch(`/cashier/payment/check/${id}`)
            .then(r => r.json())
            .then(d => {
                if(d.status === 'paid') {
                    // STOP POLLING IMMEDIATELY
                    clearInterval(paymentCheckInterval);
                    paymentCheckInterval = null; 

                    const statusEl = document.getElementById('payment-status');
                    const spinner = document.getElementById('payment-spinner');
                    
                    if(statusEl) {
                        statusEl.className = 'text-success fw-bold';
                        statusEl.innerText = 'PAYMENT RECEIVED!';
                    }
                    if(spinner) spinner.style.display = 'none';

                    setTimeout(() => {
                        // Close QR Modal
                        bootstrap.Modal.getInstance(document.getElementById('qrPaymentModal')).hide();
                        
                        // FIX: Call confirmTransaction directly for digital payments
                        // Do NOT call handlePayNow() because it triggers confirm() dialog
                        confirmTransaction('digital'); 
                    }, 1500);
                }
            });
        }, 3000);
    }

    // --- CUSTOMER & DEBT LOGIC ---
    document.getElementById('customer-id').addEventListener('change', function() {
        const type = this.value;
        const paySelect = document.getElementById('payment-method');
        const creditOpt = document.getElementById('opt-credit');
        const selectedOption = this.options[this.selectedIndex];

        Array.from(paySelect.options).forEach(opt => opt.disabled = false);

        if (type === 'new') {
            creditOpt.disabled = false; 
            paySelect.value = 'credit';
            Array.from(paySelect.options).forEach(opt => { if(opt.value !== 'credit') opt.disabled = true; });
        } 
        else if (type === 'walk-in') {
            creditOpt.disabled = true; 
            if(paySelect.value === 'credit') paySelect.value = 'cash';
        }

        currentCustomerPoints = parseInt(selectedOption.getAttribute('data-points') || 0);
        const badge = document.getElementById('redemption-section');
        const availPoints = document.getElementById('avail-points');
        const debtSection = document.getElementById('debt-section');
        const balance = parseFloat(selectedOption.getAttribute('data-balance') || 0);

        if (loyaltyEnabled == 1 && type !== 'walk-in' && type !== 'new') {
            if(availPoints) availPoints.innerText = currentCustomerPoints;
            if(badge) badge.style.display = 'block';
        } else {
            if(badge) badge.style.display = 'none';
        }

        if (debtSection) {
            if (balance > 0) {
                debtSection.style.display = 'flex';
                document.getElementById('customer-balance').innerText = balance.toLocaleString(undefined, {minimumFractionDigits: 2});
            } else {
                debtSection.style.display = 'none';
            }
        }
        toggleFlow();
    });

    function openDebtModal() {
        const select = document.getElementById('customer-id');
        const name = select.options[select.selectedIndex].text;
        const balance = document.getElementById('customer-balance').innerText;
        openDebtPaymentModal(select.value, name, balance.replace(/,/g,'')); 
    }

    // --- DEBT LIST LOGIC ---
    function openDebtorList() { new bootstrap.Modal(document.getElementById('debtorListModal')).show(); }
    function filterDebtors() {
        const query = document.getElementById('debtor-search').value.toLowerCase();
        document.querySelectorAll('.debtor-row').forEach(row => {
            row.style.display = row.getAttribute('data-name').includes(query) ? '' : 'none';
        });
    }
    
    function openDebtPaymentModal(id, name, balance) {
        const listEl = document.getElementById('debtorListModal');
        const listModal = bootstrap.Modal.getInstance(listEl);
        if(listModal) {
            listModal.hide();
            listEl.addEventListener('hidden.bs.modal', () => showPayModal(id, name, balance), {once:true});
        } else {
            showPayModal(id, name, balance);
        }
    }

    function showPayModal(id, name, balance) {
        document.getElementById('pay-debt-customer-id').value = id;
        document.getElementById('pay-debt-name').innerText = name;
        document.getElementById('pay-debt-balance').innerText = parseFloat(balance).toFixed(2);
        document.getElementById('pay-debt-amount').value = '';
        document.getElementById('pay-debt-tendered').value = '';
        document.getElementById('pay-debt-change').innerText = '0.00';
        new bootstrap.Modal(document.getElementById('debtPaymentModal')).show();
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
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ customer_id: id, amount: amount })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) { alert("Payment Collected!"); location.reload(); } 
            else { alert("Error: " + data.message); }
        })
        .catch(err => alert("Error: " + err));
    }

    // --- POS CORE LOGIC ---
    function addToCart(product) {
        const item = cart.find(i => i.id === product.id);
        if (item) {
            if(item.qty < product.stock) item.qty++;
            else { alert('Not enough stock!'); return; }
        } else {
            if(product.stock > 0) cart.push({ id: product.id, name: product.name, price: parseFloat(product.price), qty: 1, max_stock: product.stock, unit: product.unit });
            else { alert('Out of stock!'); return; }
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
                    <div><h6 class="m-0 fw-bold">${item.name}</h6><small class="text-muted">${item.unit||''} ₱${item.price} x ${item.qty}</small></div>
                    <div class="d-flex align-items-center"><span class="text-success fw-bold me-3">₱${(item.price * item.qty).toFixed(2)}</span>
                    <div class="btn-group btn-group-sm"><button class="btn btn-outline-secondary" onclick="updateQty(${item.id}, -1)">-</button><button class="btn btn-outline-secondary" onclick="updateQty(${item.id}, 1)">+</button></div></div>
                </li>`;
        });
        calculateTotalWithPoints();
    }

    function clearCart() { if(confirm("Clear cart?")) { cart = []; updateCartUI(); } }

    function calculateTotalWithPoints() {
        let subtotal = 0;
        cart.forEach(item => subtotal += item.price * item.qty);
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

    function toggleFlow() {
        const method = document.getElementById('payment-method').value;
        ['cash', 'digital', 'credit'].forEach(m => {
            const el = document.getElementById('flow-'+m);
            if(el) el.style.display = (method === m) ? 'block' : 'none';
        });
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
        if (method === 'credit') { new bootstrap.Modal(document.getElementById('creditModal')).show(); } 
        else if (confirm("Process Payment?")) { confirmTransaction(method); }
    }

    // UPDATED: Now accepts method argument explicitly
    function confirmTransaction(method) {
        let creditData = {};
        const customerVal = document.getElementById('customer-id').value;
        if (method === 'credit') {
             const name = document.getElementById('credit-name').value;
             const dueDate = document.getElementById('credit-due-date').value;
             if(!name || !dueDate) { alert("Name & Date required"); return; }
             creditData = { name: name, address: document.getElementById('credit-address').value, contact: document.getElementById('credit-contact').value, due_date: dueDate };
        }
        
        let pointsUsed = 0;
        const pointsInputEl = document.getElementById('points-to-use');
        if(pointsInputEl) pointsUsed = parseInt(pointsInputEl.value) || 0;

        const data = {
            cart: cart,
            total_amount: document.getElementById('total-amount').innerText.replace(/,/g, ''),
            points_used: pointsUsed,
            amount_paid: method === 'cash' ? document.getElementById('amount-paid').value : 0,
            payment_method: method,
            customer_id: customerVal,
            reference_number: document.getElementById('reference-number').value,
            credit_details: creditData
        };

        if (!navigator.onLine) { saveOffline(data); return; }

        fetch("{{ route('cashier.store') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                localStorage.removeItem('pos_cart'); cart = [];
                if (confirm("Success! Print Receipt?")) window.open(`/cashier/receipt/${data.sale_id}`, '_blank', 'width=400,height=600');
                location.reload();
            } else { alert("Error: " + data.message); }
        })
        .catch(err => { saveOffline(data); });
    }

    // --- UTILS ---
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    function updateOnlineStatus() {
        const queue = JSON.parse(localStorage.getItem('offline_sales')) || [];
        const alertBox = document.getElementById('offline-alert');
        if (!navigator.onLine) { alertBox.style.display = 'block'; document.getElementById('pending-count').innerText = queue.length + ' Pending'; } 
        else if (queue.length > 0) { alertBox.style.display = 'block'; alertBox.classList.replace('alert-warning', 'alert-info'); alertBox.innerHTML = `<span>Online! Syncing ${queue.length}...</span> <button class="btn btn-sm btn-light" onclick="syncOfflineSales()">Sync</button>`; syncOfflineSales(); } 
        else { alertBox.style.display = 'none'; }
    }
    function saveOffline(data) {
        let queue = JSON.parse(localStorage.getItem('offline_sales')) || [];
        queue.push(data); localStorage.setItem('offline_sales', JSON.stringify(queue)); localStorage.removeItem('pos_cart');
        alert("Saved Offline!"); location.reload();
    }
    async function syncOfflineSales() {
        let queue = JSON.parse(localStorage.getItem('offline_sales')) || [];
        if (queue.length === 0) return;
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        for (let i = 0; i < queue.length; i++) {
            await fetch("{{ route('cashier.store') }}", { method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf }, body: JSON.stringify(queue[i]) });
        }
        localStorage.removeItem('offline_sales'); alert("Sync Complete!"); location.reload();
    }
    
    // Barcode Search
    document.getElementById('product-search').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (query) {
                const exactMatch = @json($products).find(p => p.sku === query);
                if (exactMatch) { addToCart(exactMatch); this.value = ''; }
            }
        }
    });
    // Filter
    document.getElementById('product-search').addEventListener('keyup', function(e) {
        if(e.key === 'Enter') return; 
        const val = this.value.toLowerCase();
        document.querySelectorAll('.product-card').forEach(card => {
            const name = card.getAttribute('data-name');
            const sku = card.getAttribute('data-sku') || '';
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
    
    // Camera
    function openCameraModal() { new bootstrap.Modal(document.getElementById('cameraModal')).show(); if (!html5QrcodeScanner) { html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 }, false); html5QrcodeScanner.render((txt) => { const prod = @json($products).find(p => p.sku === txt); if(prod) { addToCart(prod); alert("Added "+prod.name); } }); } }
    function stopCamera() { if(html5QrcodeScanner) html5QrcodeScanner.clear(); }
</script>
@endsection