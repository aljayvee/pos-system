@extends('cashier.layout')

@section('content')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<div class="container-fluid p-3">
    <div class="row g-3">
        
        <div class="col-md-7">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex gap-2">
                        <div class="input-group input-group-lg flex-grow-1">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search"></i></span>
                            <input type="text" id="product-search" class="form-control border-start-0 bg-light" 
                                   placeholder="Scan Barcode or Search Item..." autofocus autocomplete="off">
                        </div>
                        
                        <button class="btn btn-outline-dark" onclick="openCameraModal()">
                            <i class="fas fa-camera"></i> <span class="d-none d-md-inline">Scan</span>
                        </button>
                    </div>
                </div>

                {{-- CATEGORY FILTER TOOLBAR --}}
                <div class="px-3 py-2 bg-light border-bottom overflow-auto text-nowrap" style="white-space: nowrap;">
                    <button class="btn btn-dark rounded-pill me-1 category-filter active" onclick="filterCategory('all', this)">
                        All Items
                    </button>
                    @foreach($categories as $cat)
                        <button class="btn btn-outline-secondary rounded-pill me-1 category-filter" 
                                onclick="filterCategory('{{ strtolower($cat->name) }}', this)">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>

                <div class="card-body p-0 overflow-auto" style="height: 70vh;">
                    <div class="row g-3 p-3" id="product-list">
                        @foreach($products as $product)
                        <div class="col-md-4 col-sm-6 product-card" 
                             data-name="{{ strtolower($product->name) }}" 
                             data-sku="{{ $product->sku }}"
                             data-category="{{ strtolower($product->category->name ?? '') }}"> {{-- NEW DATA ATTRIBUTE --}}
                            <div class="card h-100 shadow-sm border-0 product-item" onclick='addToCart(@json($product))' style="cursor: pointer; transition: all 0.2s;">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <h6 class="fw-bold text-dark mb-1">{{ $product->name }}</h6>
                                    
                                    {{-- NEW: UNIT DISPLAY --}}
                                    <span class="badge bg-light text-dark border mb-2">{{ ucfirst($product->unit) }}</span>
                                    
                                    <small class="text-muted mb-2">{{ $product->category->name ?? 'General' }}</small>
                                    <h5 class="text-primary fw-bold mb-0">₱{{ number_format($product->price, 2) }}</h5>
                                    @if($product->stock <= 5)
                                        <span class="badge bg-danger mt-2">Low Stock: {{ $product->stock }}</span>
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
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="m-0"><i class="fas fa-shopping-cart me-2"></i> Current Order</h5>
                </div>
                
                <div class="card-body p-0 overflow-auto flex-grow-1" style="max-height: 40vh;">
                    <ul class="list-group list-group-flush" id="cart-items">
                        <li class="list-group-item text-center text-muted mt-5 border-0">Cart is empty</li>
                    </ul>
                </div>

                <div class="card-footer bg-light p-3 border-top">
                    
                    <div id="redemption-section" class="mb-3 p-2 border rounded bg-light" style="display: none;">
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
                            <option value="new" data-points="0">+ New Customer (Credit)</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" 
                                        data-name="{{ $customer->name }}"
                                        data-contact="{{ $customer->contact }}"
                                        data-address="{{ $customer->address }}"
                                        data-points="{{ $customer->points }}">
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        <div id="loyalty-badge" class="mt-2" style="display: none;">
                            <span class="badge bg-warning text-dark border border-dark">
                                <i class="fas fa-star"></i> Loyalty Points: <span id="customer-points" class="fw-bold">0</span>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted">PAYMENT METHOD</label>
                        <select id="payment-method" class="form-select form-select-lg" onchange="toggleFlow()">
                            <option value="cash">Cash Payment</option>
                            <option value="digital">Digital Wallet (Gcash/Maya)</option>
                            <option value="credit" disabled id="opt-credit">Credit (Utang)</option>
                        </select>
                    </div>

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
                        <div class="alert alert-warning py-2 small"><i class="fas fa-info-circle"></i> Complete details in popup.</div>
                    </div>

                    <button class="btn btn-success w-100 btn-lg fw-bold" onclick="handlePayNow()">
                        <i class="fas fa-check-circle me-2"></i> PAY NOW
                    </button>
                </div>
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
                <p class="text-muted mt-2">Point camera at product barcode</p>
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
                <label class="small text-muted">Promise to Pay Date:</label>
                <input type="date" id="credit-due-date" class="form-control">
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary w-100" onclick="confirmTransaction('credit')">Confirm Credit</button>
            </div>
        </div>
    </div>
</div>

<style>
    .product-item:hover { transform: translateY(-3px); background-color: #f8f9fa; }
    /* Hide scrollbar for cleaner look */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; }
    ::-webkit-scrollbar-thumb { background: #888; border-radius: 3px; }
</style>

<script>
    // --- CONFIGURATION ---
    const pointsValue = {{ \App\Models\Setting::where('key', 'points_conversion')->value('value') ?? 1 }};
    const loyaltyEnabled = {{ \App\Models\Setting::where('key', 'enable_loyalty')->value('value') ?? 0 }};
    
    let cart = [];
    let html5QrcodeScanner = null;
    let currentCustomerPoints = 0; 

    // --- INITIALIZATION ---
    window.onload = () => {
        const searchInput = document.getElementById('product-search');
        if(searchInput) searchInput.focus();
    };

    // --- CUSTOMER & LOYALTY LOGIC ---
    document.getElementById('customer-id').addEventListener('change', function() {
        const type = this.value;
        const selectedOption = this.options[this.selectedIndex];
        
        currentCustomerPoints = parseInt(selectedOption.getAttribute('data-points') || 0);
        
        const badge = document.getElementById('loyalty-badge');
        const redemptionBox = document.getElementById('redemption-section');
        const pointsSpan = document.getElementById('customer-points');
        const availPointsSpan = document.getElementById('avail-points');

        if (loyaltyEnabled == 1 && type !== 'walk-in' && type !== 'new') {
            if(pointsSpan) pointsSpan.innerText = currentCustomerPoints;
            if(availPointsSpan) availPointsSpan.innerText = currentCustomerPoints;
            
            if(badge) badge.style.display = 'block';
            if(redemptionBox) redemptionBox.style.display = 'block';
        } else {
            if(badge) badge.style.display = 'none';
            if(redemptionBox) redemptionBox.style.display = 'none';
        }
        
        const pointsInput = document.getElementById('points-to-use');
        if(pointsInput) pointsInput.value = '';
        calculateTotalWithPoints(); 

        const paySelect = document.getElementById('payment-method');
        const creditOpt = document.getElementById('opt-credit');
        
        if (type !== 'walk-in') {
            paySelect.value = 'credit';
            if(creditOpt) creditOpt.disabled = false;
        } else {
            paySelect.value = 'cash';
            if(creditOpt) creditOpt.disabled = true;
        }
        toggleFlow();
    });

    // --- LOYALTY CALCULATION ---
    function calculateTotalWithPoints() {
        let subtotal = 0;
        cart.forEach(item => subtotal += item.price * item.qty);
        
        const pointsInputEl = document.getElementById('points-to-use');
        let pointsInput = 0;
        
        if (pointsInputEl) {
            pointsInput = parseInt(pointsInputEl.value) || 0;

            if (pointsInput > currentCustomerPoints) {
                pointsInput = currentCustomerPoints;
                pointsInputEl.value = pointsInput;
            }

            let discount = pointsInput * pointsValue;
            
            if (discount > subtotal) {
                discount = subtotal;
                pointsInput = Math.ceil(discount / pointsValue);
                pointsInputEl.value = pointsInput;
            }

            const discDisplay = document.getElementById('discount-display');
            if(discDisplay) discDisplay.innerText = discount.toFixed(2);
        }

        let discountAmount = pointsInput * pointsValue;
        let finalTotal = subtotal - discountAmount;
        if(finalTotal < 0) finalTotal = 0;

        document.getElementById('total-amount').innerText = finalTotal.toFixed(2);
        calculateChange();
    }

    // --- BARCODE SCANNER (USB/Keyboard) ---
    document.getElementById('product-search').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (query) {
                const exactMatch = @json($products).find(p => p.sku === query);
                if (exactMatch) {
                    addToCart(exactMatch);
                    this.value = ''; 
                }
            }
        }
    });

    // --- BARCODE SCANNER (Camera) ---
    function openCameraModal() {
        const modalEl = document.getElementById('cameraModal');
        if(!modalEl) return;
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        
        const config = { 
            fps: 10, 
            qrbox: { width: 300, height: 150 }, 
            aspectRatio: 1.0,
            formatsToSupport: [ 
                Html5QrcodeSupportedFormats.UPC_A, 
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8, 
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39
            ],
            experimentalFeatures: { useBarCodeDetectorIfSupported: true }
        };

        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5QrcodeScanner("reader", config, false);
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        const product = @json($products).find(p => p.sku === decodedText);
        if (product) {
            addToCart(product);
            alert("Added: " + product.name); 
        } else {
            alert("Product not found: " + decodedText);
        }
    }

    function onScanFailure(error) {
    }

    function stopCamera() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
            html5QrcodeScanner = null;
        }
    }

    // --- SEARCH FILTER ---
    document.getElementById('product-search').addEventListener('keyup', function(e) {
        if(e.key === 'Enter') return; 
        const val = this.value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        let hasVisible = false;

        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            const sku = card.getAttribute('data-sku') || '';
            if (name.includes(val) || sku.includes(val)) {
                card.style.display = 'block';
                hasVisible = true;
            } else {
                card.style.display = 'none';
            }
        });
        document.getElementById('no-products').style.display = hasVisible ? 'none' : 'block';
    });

    // --- CART FUNCTIONS ---
    function addToCart(product) {
        const existingItem = cart.find(item => item.id === product.id);
        if (existingItem) {
            if(existingItem.qty < product.stock) existingItem.qty++;
            else { alert('Not enough stock!'); return; }
        } else {
            if(product.stock > 0) {
                cart.push({ 
                    id: product.id, 
                    name: product.name, 
                    price: parseFloat(product.price), 
                    qty: 1, 
                    max_stock: product.stock 
                });
            } else { 
                alert('Out of stock!'); return; 
            }
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
        
        if (cart.length === 0) list.innerHTML = '<li class="list-group-item text-center text-muted mt-5 border-0">Cart is empty</li>';

        cart.forEach(item => {
            list.innerHTML += `
                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
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
        
        calculateTotalWithPoints();
    }

    // --- PAYMENT FLOW & CALCULATIONS ---
    function toggleFlow() {
        const method = document.getElementById('payment-method').value;
        ['cash', 'digital', 'credit'].forEach(m => {
            const el = document.getElementById('flow-'+m);
            if(el) el.style.display = (method === m) ? 'block' : 'none';
        });
    }

    function calculateChange() {
        const totalText = document.getElementById('total-amount').innerText;
        const total = parseFloat(totalText.replace(/,/g, '')) || 0; 
        const paid = parseFloat(document.getElementById('amount-paid').value) || 0;
        const change = paid - total;
        document.getElementById('change-display').innerText = change >= 0 ? '₱' + change.toFixed(2) : 'Invalid';
    }

    // --- TRANSACTION HANDLING ---
    function handlePayNow() {
        if (cart.length === 0) { alert("Cart is empty!"); return; }
        const method = document.getElementById('payment-method').value;

        if (method === 'credit') {
            const creditModal = new bootstrap.Modal(document.getElementById('creditModal'));
            creditModal.show();
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
             if(!name || !dueDate) { alert("Missing Credit Details (Name & Date required)"); return; }
             
             creditData = { 
                 is_new: customerVal === 'new', 
                 name: name, 
                 address: document.getElementById('credit-address').value, 
                 contact: document.getElementById('credit-contact').value, 
                 due_date: dueDate 
             };
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

        fetch("{{ route('cashier.store') }}", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json", 
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (confirm("Success! Print Receipt?")) {
                    window.open(`/cashier/receipt/${data.sale_id}`, '_blank', 'width=400,height=600');
                }
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert("Transaction failed. Check console.");
        });
    }


    // --- CATEGORY FILTER LOGIC ---
    function filterCategory(category, btnElement) {
        // 1. Update Buttons Visual State
        document.querySelectorAll('.category-filter').forEach(btn => {
            btn.classList.remove('btn-dark', 'active');
            btn.classList.add('btn-outline-secondary');
        });
        btnElement.classList.remove('btn-outline-secondary');
        btnElement.classList.add('btn-dark', 'active');

        // 2. Filter Products
        const cards = document.querySelectorAll('.product-card');
        const noProductsMsg = document.getElementById('no-products');
        let hasVisible = false;

        cards.forEach(card => {
            const productCat = card.getAttribute('data-category');
            
            if (category === 'all' || productCat === category) {
                card.style.display = 'block';
                hasVisible = true;
            } else {
                card.style.display = 'none';
            }
        });

        // 3. Show/Hide Empty State
        noProductsMsg.style.display = hasVisible ? 'none' : 'block';
    }
</script>
@endsection