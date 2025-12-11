@extends('cashier.layout')

@section('content')
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0">
                <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-box-open"></i> Products</h5>
            </div>
            <div class="card-body bg-light">
                <div class="row" id="product-list">
                    @foreach($products as $product)
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card product-card h-100 border-0 shadow-sm" onclick="addToCart({{ $product }})">
                            <div class="card-body text-center d-flex flex-column justify-content-center p-3">
                                <h6 class="card-title font-weight-bold mb-2">{{ $product->name }}</h6>
                                <span class="badge bg-primary fs-6 mb-2">₱{{ number_format($product->price, 2) }}</span>
                                <small class="text-muted">Stock: {{ $product->stock }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Order</h5>
                <span id="cart-count" class="badge bg-danger rounded-pill">0</span>
            </div>
            
            <div class="card-body p-0 d-flex flex-column">
                <ul class="list-group list-group-flush scrollable-cart flex-grow-1" id="cart-items" style="max-height: 350px; overflow-y: auto;">
                    <li class="list-group-item text-center text-muted mt-5 border-0">Select items to add</li>
                </ul>
            </div>

            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-between mb-3 align-items-center">
                    <h4 class="fw-light">Total:</h4>
                    <h3 class="text-success fw-bold">₱<span id="total-amount">0.00</span></h3>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold small">1. Customer</label>
                    <select class="form-select" id="customer-id">
                        <option value="walk-in">Walk-in Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" 
                                    data-name="{{ $customer->name }}" 
                                    data-contact="{{ $customer->contact }}"
                                    data-address="{{ $customer->address }}">
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">2. Payment Method</label>
                    <select class="form-select" id="payment-method" onchange="toggleFlow()">
                        <option value="cash">Cash</option>
                        <option value="digital">Digital Payment (Gcash/PayMaya)</option>
                        <option value="credit">Credit (Utang)</option>
                    </select>
                </div>

                <div id="flow-cash">
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Enter Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" id="amount-paid" class="form-control" placeholder="0.00" oninput="calculateChange()">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Change:</span>
                        <span class="fw-bold" id="change-display">₱0.00</span>
                    </div>
                </div>

                <div id="flow-digital" style="display: none;">
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Reference Number</label>
                        <input type="text" id="reference-number" class="form-control" placeholder="e.g. 123456789">
                    </div>
                </div>

                <div id="flow-credit" style="display: none;">
                    <div class="alert alert-info py-2 small">
                        <i class="fas fa-arrow-right"></i> Click "Pay Now" to fill out credit forms.
                    </div>
                </div>

                <div class="d-grid mt-3">
                    <button class="btn btn-success btn-lg py-3 shadow-sm" onclick="handlePayNow()">
                        <i class="fas fa-check-circle me-2"></i> PAY NOW
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="creditModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice"></i> Credit Application Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Full Name</label>
                    <input type="text" id="credit-name" class="form-control bg-light" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Address</label>
                    <input type="text" id="credit-address" class="form-control" placeholder="Enter complete address">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Contact Number</label>
                    <input type="text" id="credit-contact" class="form-control" placeholder="09xxxxxxxxx">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold text-danger">Exact Date of Pay</label>
                    <input type="date" id="credit-due-date" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmTransaction('credit')">Confirm Transaction</button>
            </div>
        </div>
    </div>
</div>

<script>
    let cart = [];

    // --- CART FUNCTIONS ---
    function addToCart(product) {
        const existingItem = cart.find(item => item.id === product.id);
        if (existingItem) {
            if(existingItem.qty < product.stock) existingItem.qty++;
            else { alert('Not enough stock!'); return; }
        } else {
            if(product.stock > 0) cart.push({ id: product.id, name: product.name, price: parseFloat(product.price), qty: 1, max_stock: product.stock });
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
        const list = document.getElementById('cart-items');
        list.innerHTML = '';
        let total = 0;
        
        if (cart.length === 0) list.innerHTML = '<li class="list-group-item text-center text-muted mt-5 border-0">Cart is empty</li>';

        cart.forEach(item => {
            total += item.price * item.qty;
            list.innerHTML += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div><h6 class="m-0 fw-bold">${item.name}</h6><small>₱${item.price} x ${item.qty}</small></div>
                    <div class="d-flex align-items-center">
                        <span class="text-success fw-bold me-3">₱${(item.price * item.qty).toFixed(2)}</span>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary" onclick="updateQty(${item.id}, -1)">-</button>
                            <button class="btn btn-outline-secondary" onclick="updateQty(${item.id}, 1)">+</button>
                        </div>
                    </div>
                </li>`;
        });
        document.getElementById('total-amount').innerText = total.toFixed(2);
        calculateChange();
    }

    // --- FLOW LOGIC ---
    function toggleFlow() {
        const method = document.getElementById('payment-method').value;
        document.getElementById('flow-cash').style.display = method === 'cash' ? 'block' : 'none';
        document.getElementById('flow-digital').style.display = method === 'digital' ? 'block' : 'none';
        document.getElementById('flow-credit').style.display = method === 'credit' ? 'block' : 'none';
    }

    function calculateChange() {
        const total = parseFloat(document.getElementById('total-amount').innerText);
        const paid = parseFloat(document.getElementById('amount-paid').value) || 0;
        const change = paid - total;
        document.getElementById('change-display').innerText = change >= 0 ? '₱' + change.toFixed(2) : 'Invalid';
    }

    // --- MAIN PAYMENT HANDLER ---
    function handlePayNow() {
        if (cart.length === 0) { alert("Cart is empty!"); return; }

        const method = document.getElementById('payment-method').value;
        const customerSelect = document.getElementById('customer-id');
        const customerId = customerSelect.value;
        const total = parseFloat(document.getElementById('total-amount').innerText);

        // FLOW 3: CREDIT (Specific Form Logic)
        if (method === 'credit') {
            if (customerId === 'walk-in') {
                alert("Walk-in customers cannot avail Credit. Please select a registered customer.");
                return;
            }
            // Populate Modal
            const option = customerSelect.options[customerSelect.selectedIndex];
            document.getElementById('credit-name').value = option.getAttribute('data-name');
            document.getElementById('credit-contact').value = option.getAttribute('data-contact') || '';
            document.getElementById('credit-address').value = option.getAttribute('data-address') || '';
            document.getElementById('credit-due-date').value = ''; 
            
            // Show Modal
            new bootstrap.Modal(document.getElementById('creditModal')).show();
            return; 
        }

        // FLOW 1: CASH VALIDATION
        if (method === 'cash') {
            const paid = parseFloat(document.getElementById('amount-paid').value);
            if (!paid || paid < total) { alert("Insufficient Cash Amount!"); return; }
        }

        // FLOW 2: DIGITAL VALIDATION
        if (method === 'digital') {
            const ref = document.getElementById('reference-number').value;
            if (!ref) { alert("Please enter Reference Number!"); return; }
        }

        // Confirmation Dialog for Cash/Digital
        if(confirm("Process this transaction?")) {
            submitTransaction(method);
        }
    }

    // --- SUBMIT TO BACKEND ---
    function confirmTransaction(method) {
        // If coming from Credit Modal, validate modal fields first
        let creditData = {};
        if (method === 'credit') {
            const dueDate = document.getElementById('credit-due-date').value;
            if (!dueDate) { alert("Please enter Exact Date of Pay."); return; }
            
            creditData = {
                due_date: dueDate,
                address: document.getElementById('credit-address').value,
                contact: document.getElementById('credit-contact').value
            };
            
            if(!confirm("Process Credit Transaction?")) return;
        }

        const data = {
            cart: cart,
            total_amount: document.getElementById('total-amount').innerText,
            amount_paid: method === 'cash' ? document.getElementById('amount-paid').value : 0,
            payment_method: method,
            customer_id: document.getElementById('customer-id').value === 'walk-in' ? null : document.getElementById('customer-id').value,
            reference_number: document.getElementById('reference-number').value,
            credit_details: creditData
        };

        fetch("{{ route('cashier.store') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Transaction Complete!");
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => console.error(err));
    }
</script>
@endsection