@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4">
    
    {{-- MOBILE HEADER --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm px-3 py-3 d-flex align-items-center justify-content-between z-3 mb-3" style="top: 0;">
        <a href="{{ route('purchases.index') }}" class="text-dark"><i class="fas fa-times"></i></a>
        <h6 class="m-0 fw-bold text-dark">New Stock In</h6>
        <div style="width: 24px;"></div>
    </div>

    {{-- DESKTOP HEADER --}}
    <div class="d-none d-lg-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold text-dark mb-0"><i class="fas fa-cart-plus text-success me-2"></i>Restock Inventory</h4>
        <a href="{{ route('purchases.index') }}" class="btn btn-light border shadow-sm rounded-pill fw-bold">
            <i class="fas fa-times me-1"></i> Cancel
        </a>
    </div>

    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
        @csrf

        <div class="row g-4 mb-5 pb-5 mb-lg-0 pb-lg-0">
            
            {{-- SUPPLIER & DETAILS CARD --}}
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-success bg-opacity-10 text-success py-3 border-0">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>1. Supplier Details</h5>
                    </div>
                    <div class="card-body p-4">
                         @if($errors->any())
                            <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Purchase Date</label>
                                <input type="date" name="purchase_date" class="form-control bg-light border-0 py-3" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Select Supplier</label>
                                <div class="input-group shadow-sm rounded-4 overflow-hidden">
                                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-user-tie text-secondary"></i></span>
                                    <select name="supplier_id" id="supplier_select" class="form-select border-0 bg-white shadow-none py-3">
                                        <option value="">-- Choose Existing --</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Or New Supplier</label>
                                <input type="text" name="new_supplier_name" class="form-control bg-light border-0 py-3" placeholder="Type to create new...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ITEMS REPEATER --}}
            <div class="col-12">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 border-bottom border-light">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-cubes me-2 text-primary"></i>2. Items to Restock</h5>
                        <div class="bg-success text-white px-4 py-2 rounded-pill shadow-sm d-flex align-items-center justify-content-between" style="min-width: 200px;">
                            <span class="small opacity-75 me-2">EST. TOTAL</span>
                            <span id="grandTotal" class="fw-bold fs-5">₱0.00</span>
                        </div>
                    </div>
                    <div class="card-body p-0 p-md-4 bg-light">
                        
                        {{-- HEADERS (Hidden on Mobile) --}}
                        <div class="d-none d-lg-flex row g-2 px-3 mb-2 fw-bold text-secondary small text-uppercase items-header">
                            <div class="col-5">Product</div>
                            <div class="col-3">Quantity</div>
                            <div class="col-3">Unit Cost</div>
                            <div class="col-1"></div>
                        </div>

                        <div id="items_container">
                            {{-- Row 0 --}}
                            <div class="item-row card card-body shadow-sm border-0 mb-3 rounded-4 px-3 py-4" id="row_0">
                                <div class="row g-3 align-items-end align-items-lg-center">
                                    {{-- Product --}}
                                    <div class="col-12 col-lg-5">
                                        <label class="form-label d-lg-none fw-bold small text-secondary text-uppercase">Product</label>
                                        <select name="items[0][product_id]" class="form-select bg-light border-0 product-select py-3" required onchange="updateCost(this)">
                                            <option value="" data-cost="0">Select Product...</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-cost="{{ $product->cost ?? 0 }}">
                                                    {{ $product->name }} (Cur: {{ $product->stock }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- Qty --}}
                                    <div class="col-6 col-lg-3">
                                        <label class="form-label d-lg-none fw-bold small text-secondary text-uppercase">Quantity</label>
                                        <input type="number" name="items[0][quantity]" class="form-control bg-light border-0 qty-input fw-bold text-center py-3" placeholder="0" min="1" required oninput="calculateTotal()" inputmode="numeric">
                                    </div>
                                    {{-- Cost --}}
                                    <div class="col-6 col-lg-3">
                                        <label class="form-label d-lg-none fw-bold small text-secondary text-uppercase">Unit Cost</label>
                                        <div class="input-group shadow-sm rounded-4 overflow-hidden">
                                            <span class="input-group-text bg-white border-0 px-3 text-secondary">₱</span>
                                            <input type="number" name="items[0][unit_cost]" class="form-control bg-white border-0 cost-input fw-bold py-3" placeholder="0.00" step="0.01" min="0" required oninput="calculateTotal()" inputmode="decimal">
                                        </div>
                                    </div>
                                    {{-- Remove --}}
                                    <div class="col-12 col-lg-1 text-end text-lg-center">
                                        <button type="button" class="btn btn-outline-danger w-100 w-lg-auto rounded-pill border-0 bg-danger-subtle text-danger py-2" onclick="removeRow(this)" disabled>
                                            <i class="fas fa-trash"></i> <span class="d-lg-none ms-2">Remove Item</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="button" class="btn btn-white border-2 border-dashed border-primary text-primary fw-bold py-3 rounded-4 hover-shadow" onclick="addRow()">
                                <i class="fas fa-plus-circle me-2"></i> Add Another Item
                            </button>
                        </div>

                    </div>
                    
                    {{-- DESKTOP FOOTER --}}
                    <div class="card-footer bg-white py-4 border-top-0 d-none d-lg-flex justify-content-end">
                        <button type="submit" class="btn btn-success rounded-pill px-5 py-3 fw-bold shadow-lg text-uppercase tracking-wide">
                            <i class="fas fa-check-circle me-2"></i> Confirm Stock In
                        </button>
                    </div>
                </div>
            </div>

        </div>

        {{-- MOBILE STICKY BOTTOM BAR --}}
        <div class="d-lg-none position-fixed bottom-0 start-0 w-100 bg-white border-top p-3 z-3 shadow-lg">
            <button type="submit" class="btn btn-dark w-100 py-3 rounded-pill fw-bold shadow-lg">
                <i class="fas fa-check-circle me-2"></i> Confirm Stock In
            </button>
        </div>
    </form>
</div>

<script>
    let rowCount = 1;

    // Use a JS template literal for cleaner HTML injection
    function addRow() {
        const container = document.getElementById('items_container');
        const newRowHTML = `
            <div class="item-row card card-body shadow-sm border-0 mb-3 rounded-4 px-3 py-4 animate__animated animate__fadeIn" id="row_${rowCount}">
                <div class="row g-3 align-items-end align-items-lg-center">
                    <div class="col-12 col-lg-5">
                        <label class="form-label d-lg-none fw-bold small text-secondary text-uppercase">Product</label>
                        <select name="items[${rowCount}][product_id]" class="form-select bg-light border-0 product-select py-3" required onchange="updateCost(this)">
                            <option value="" data-cost="0">Select Product...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-cost="{{ $product->cost ?? 0 }}">
                                    {{ $product->name }} (Cur: {{ $product->stock }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="form-label d-lg-none fw-bold small text-secondary text-uppercase">Quantity</label>
                        <input type="number" name="items[${rowCount}][quantity]" class="form-control bg-light border-0 qty-input fw-bold text-center py-3" placeholder="0" min="1" required oninput="calculateTotal()" inputmode="numeric">
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="form-label d-lg-none fw-bold small text-secondary text-uppercase">Unit Cost</label>
                        <div class="input-group shadow-sm rounded-4 overflow-hidden">
                            <span class="input-group-text bg-white border-0 px-3 text-secondary">₱</span>
                            <input type="number" name="items[${rowCount}][unit_cost]" class="form-control bg-white border-0 cost-input fw-bold py-3" placeholder="0.00" step="0.01" min="0" required oninput="calculateTotal()" inputmode="decimal">
                        </div>
                    </div>
                    <div class="col-12 col-lg-1 text-end text-lg-center">
                        <button type="button" class="btn btn-outline-danger w-100 w-lg-auto rounded-pill border-0 bg-danger-subtle text-danger py-2" onclick="removeRow(this)">
                            <i class="fas fa-trash"></i> <span class="d-lg-none ms-2">Remove Item</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', newRowHTML);
        rowCount++;
    }

    function removeRow(button) {
        button.closest('.item-row').remove();
        calculateTotal();
    }

    function updateCost(selectElement) {
        const cost = selectElement.options[selectElement.selectedIndex].getAttribute('data-cost');
        const row = selectElement.closest('.item-row');
        const costInput = row.querySelector('.cost-input');
        
        // Only autofill if empty to prevent overwriting user input
        if(costInput.value === '' || costInput.value == 0) {
            costInput.value = cost;
        }
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
            total += (qty * cost);
        });
        document.getElementById('grandTotal').innerText = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
</script>
@endsection