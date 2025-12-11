@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Stock Adjustment</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active">Adjust Stock</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Record Wastage, Damage, or Loss
        </div>
        <div class="card-body">
            <form action="{{ route('inventory.process') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">Select Product</label>
                    <select name="product_id" class="form-select select2" required id="product-select">
                        <option value="" selected disabled>-- Choose Item --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-stock="{{ $product->stock }}">
                                {{ $product->name }} (Current Stock: {{ $product->stock }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason for Removal</label>
                    <select name="type" class="form-select" required>
                        <option value="wastage">Wastage (Expired/Spoiled)</option>
                        <option value="damage">Damaged / Broken</option>
                        <option value="loss">Loss / Theft</option>
                        <option value="return">Customer Return (Defective)</option>
                        <option value="correction">Manual Count Correction</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Quantity to Remove</label>
                    <input type="number" name="quantity" class="form-control" min="1" required id="qty-input">
                    <div class="form-text text-danger" id="stock-warning" style="display:none;">
                        Warning: Only <span id="current-stock">0</span> items available.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks (Optional)</label>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="Additional details..."></textarea>
                </div>

                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-minus-circle"></i> Deduct Stock
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Simple script to warn if user tries to remove more than available
    document.getElementById('product-select').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const stock = parseInt(selectedOption.getAttribute('data-stock'));
        document.getElementById('current-stock').innerText = stock;
        document.getElementById('qty-input').setAttribute('max', stock);
    });
</script>
@endsection