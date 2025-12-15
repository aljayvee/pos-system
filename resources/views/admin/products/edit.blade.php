@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    <div class="d-flex align-items-center justify-content-between mt-4 mb-4">
        <div>
            <h1 class="h2 mb-0 text-gray-800">Edit Product</h1>
            <p class="text-muted small mb-0">Updating details for: <strong>{{ $product->name }}</strong></p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <form action="{{ route('products.update', $product->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary">Product Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label class="form-label fw-bold">Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Unit</label>
                                <select name="unit" class="form-select" required>
                                    @foreach(['pc','pack','kg','g','l','ml','box','bottle','can'] as $u)
                                        <option value="{{ $u }}" {{ $product->unit == $u ? 'selected' : '' }}>{{ ucfirst($u) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select" required>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-light rounded border">
                            <h6 class="fw-bold mb-3">Pricing</h6>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold">Selling Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">₱</span>
                                        <input type="number" step="0.01" name="price" class="form-control fw-bold" value="{{ $product->price }}" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Cost Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">₱</span>
                                        <input type="number" step="0.01" name="cost" class="form-control" value="{{ $product->cost }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-warning text-dark">Inventory Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Current Stock</label>
                            <input type="number" name="stock" class="form-control bg-light" value="{{ $product->stock }}">
                            <div class="form-text small">Use "Adjust Stock" tool for large changes.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reorder Point</label>
                            <input type="number" name="reorder_point" class="form-control" value="{{ $product->reorder_point }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expiration Date</label>
                            <input type="date" name="expiration_date" class="form-control" 
                                   value="{{ $product->expiration_date ? $product->expiration_date->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Barcode</h5>
                    </div>
                    <div class="card-body">
                        <label class="form-label">SKU / Code</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-barcode"></i></span>
                            <input type="text" name="sku" class="form-control" value="{{ $product->sku }}">
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                        <i class="fas fa-save me-2"></i> Update Product
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-light text-muted">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection