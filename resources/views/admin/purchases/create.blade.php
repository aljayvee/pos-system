@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
<a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Back/Cancel</a>
    <div class="d-flex align-items-center justify-content-between mt-4 mb-4">
        <h1 class="h2 mb-0"><i class="fas fa-cart-plus text-success me-2"></i>Restock Inventory</h1>
        
    </div>

    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
        @csrf

        <div class="row g-4">
            
            {{-- SUPPLIER & DETAILS CARD --}}
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-secondary">1. Supplier Details</h5>
                    </div>
                    <div class="card-body">
                         @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Purchase Date</label>
                                <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Select Supplier</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user-tie"></i></span>
                                    <select name="supplier_id" id="supplier_select" class="form-select select2">
                                        <option value="">-- Choose Existing --</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted">Or New Supplier</label>
                                <input type="text" name="new_supplier_name" class="form-control" placeholder="Type to create new...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ITEMS REPEATER --}}
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-secondary">2. Items to Restock</h5>
                        <div class="badge bg-success-subtle text-success border border-success px-3 py-2">
                            Est. Total: <span id="grandTotal" class="fw-bold">₱0.00</span>
                        </div>
                    </div>
                    <div class="card-body p-0 p-md-3 bg-light">
                        
                        {{-- HEADERS (Hidden on Mobile) --}}
                        <div class="d-none d-md-flex row g-2 px-2 mb-2 fw-bold text-muted small text-uppercase">
                            <div class="col-5">Product</div>
                            <div class="col-3">Quantity</div>
                            <div class="col-3">Unit Cost</div>
                            <div class="col-1"></div>
                        </div>

                        <div id="items_container">
                            {{-- Row 0 --}}
                            <div class="item-row card card-body shadow-sm border-0 mb-2 px-3 py-3" id="row_0">
                                <div class="row g-3 align-items-end align-items-md-center">
                                    {{-- Product --}}
                                    <div class="col-12 col-md-5">
                                        <label class="form-label d-md-none fw-bold small">Product</label>
                                        <select name="items[0][product_id]" class="form-select product-select" required onchange="updateCost(this)">
                                            <option value="" data-cost="0">Select Product...</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-cost="{{ $product->cost ?? 0 }}">
                                                    {{ $product->name }} (Cur: {{ $product->stock }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- Qty --}}
                                    <div class="col-6 col-md-3">
                                        <label class="form-label d-md-none fw-bold small">Quantity</label>
                                        <input type="number" name="items[0][quantity]" class="form-control qty-input" placeholder="0" min="1" required oninput="calculateTotal()">
                                    </div>
                                    {{-- Cost --}}
                                    <div class="col-6 col-md-3">
                                        <label class="form-label d-md-none fw-bold small">Unit Cost</label>
                                        <div class="input-group">
                                            <span class="input-group-text px-2 text-muted">₱</span>
                                            <input type="number" name="items[0][unit_cost]" class="form-control cost-input" placeholder="0.00" step="0.01" min="0" required oninput="calculateTotal()">
                                        </div>
                                    </div>
                                    {{-- Remove --}}
                                    <div class="col-12 col-md-1 text-end text-md-center">
                                        <button type="button" class="btn btn-outline-danger w-100 w-md-auto" onclick="removeRow(this)" disabled>
                                            <i class="fas fa-trash"></i> <span class="d-md-none ms-2">Remove Item</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="button" class="btn btn-outline-primary border-dashed py-2" onclick="addRow()">
                                <i class="fas fa-plus-circle me-1"></i> Add Another Item
                            </button>
                        </div>

                    </div>
                    <div class="card-footer bg-white py-3">
                        <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm">
                            <i class="fas fa-check-circle me-2"></i> Confirm Stock In
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
    let rowCount = 1;

    // Use a JS template literal for cleaner HTML injection
    function addRow() {
        const container = document.getElementById('items_container');
        const newRowHTML = `
            <div class="item-row card card-body shadow-sm border-0 mb-2 px-3 py-3 animate__animated animate__fadeIn" id="row_${rowCount}">
                <div class="row g-3 align-items-end align-items-md-center">
                    <div class="col-12 col-md-5">
                        <label class="form-label d-md-none fw-bold small">Product</label>
                        <select name="items[${rowCount}][product_id]" class="form-select product-select" required onchange="updateCost(this)">
                            <option value="" data-cost="0">Select Product...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-cost="{{ $product->cost ?? 0 }}">
                                    {{ $product->name }} (Cur: {{ $product->stock }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label d-md-none fw-bold small">Quantity</label>
                        <input type="number" name="items[${rowCount}][quantity]" class="form-control qty-input" placeholder="0" min="1" required oninput="calculateTotal()">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label d-md-none fw-bold small">Unit Cost</label>
                        <div class="input-group">
                            <span class="input-group-text px-2 text-muted">₱</span>
                            <input type="number" name="items[${rowCount}][unit_cost]" class="form-control cost-input" placeholder="0.00" step="0.01" min="0" required oninput="calculateTotal()">
                        </div>
                    </div>
                    <div class="col-12 col-md-1 text-end text-md-center">
                        <button type="button" class="btn btn-outline-danger w-100 w-md-auto" onclick="removeRow(this)">
                            <i class="fas fa-trash"></i> <span class="d-md-none ms-2">Remove</span>
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