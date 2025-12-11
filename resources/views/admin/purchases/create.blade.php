@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-box-open"></i> Restock Inventory (Stock In)</h4>
        </div>
        <div class="card-body">
            
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('purchases.store') }}" method="POST">
                @csrf
                
                {{-- 1. HEADER DETAILS --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Select Supplier</label>
                        <select name="supplier_id" id="supplier_select" class="form-select">
                            <option value="">-- Select Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Or New Supplier</label>
                        <input type="text" name="new_supplier_name" id="new_supplier" class="form-control" placeholder="Type name to create new">
                    </div>
                </div>

                <hr>

                {{-- 2. ITEMS TABLE --}}
                <h5 class="mb-3">Items to Restock</h5>
                <table class="table table-bordered" id="items_table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40%">Product</th>
                            <th>Quantity</th>
                            <th>Unit Cost (₱)</th>
                            <th style="width: 50px"></th>
                        </tr>
                    </thead>
                    <tbody id="table_body">
                        {{-- Row 1 (Default) --}}
                        <tr>
                            <td>
                                <select name="items[0][product_id]" class="form-select product-select" required onchange="updateCost(this)">
                                    <option value="" data-cost="0">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-cost="{{ $product->cost ?? 0 }}">
                                            {{ $product->name }} (Current: {{ $product->stock }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="items[0][quantity]" class="form-control" placeholder="Qty" min="1" required>
                            </td>
                            <td>
                                <input type="number" name="items[0][unit_cost]" class="form-control cost-input" placeholder="Cost" step="0.01" min="0" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)" disabled><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-primary" onclick="addRow()">
                        <i class="fas fa-plus"></i> Add Another Item
                    </button>
                    <div>
                        <a href="{{ route('purchases.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-success px-4">Confirm Restock</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JAVASCRIPT FOR DYNAMIC ROWS --}}
<script>
    let rowCount = 1;

    function addRow() {
        const tableBody = document.getElementById('table_body');
        const newRow = `
            <tr>
                <td>
                    <select name="items[${rowCount}][product_id]" class="form-select product-select" required onchange="updateCost(this)">
                        <option value="" data-cost="0">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-cost="{{ $product->cost ?? 0 }}">
                                {{ $product->name }} (Current: {{ $product->stock }})
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][quantity]" class="form-control" placeholder="Qty" min="1" required>
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][unit_cost]" class="form-control cost-input" placeholder="Cost" step="0.01" min="0" required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        tableBody.insertAdjacentHTML('beforeend', newRow);
        rowCount++;
    }

    function removeRow(button) {
        button.closest('tr').remove();
    }

    // Auto-fill the "Unit Cost" field if the product already has a cost price saved
    function updateCost(selectElement) {
        const cost = selectElement.options[selectElement.selectedIndex].getAttribute('data-cost');
        const row = selectElement.closest('tr');
        const costInput = row.querySelector('.cost-input');
        if(costInput.value === '' || costInput.value == 0) {
            costInput.value = cost;
        }
    }
</script>
@endsection