@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Edit Product: {{ $product->name }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('products.update', $product->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                        <label class="form-label">Unit</label>
                        <select name="unit" class="form-select" required>
                            @foreach(['pc','pack','kg','g','l','ml','box','bottle','can'] as $u)
                                <option value="{{ $u }}" {{ $product->unit == $u ? 'selected' : '' }}>{{ ucfirst($u) }}</option>
                            @endforeach
                        </select>
                    </div>
                           <div class="mb-3">
                            <label class="form-label">Barcode / SKU (Optional)</label>
                            <div class="input-group">
                                <input type="text" id="sku-input" name="sku" class="form-control" value="{{ $product->sku }}" placeholder="Scan or type barcode...">
                                <button type="button" class="btn btn-outline-secondary" onclick="openScanner()">
                                    <i class="fas fa-camera"></i> Scan
                                </button>
                            </div>
                        </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Selling Price</label>
                                <input type="number" step="0.01" name="price" class="form-control" value="{{ $product->price }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cost Price</label>
                                <input type="number" step="0.01" name="cost" class="form-control" value="{{ $product->cost }}">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Current Stock</label>
                                <input type="number" name="stock" class="form-control" value="{{ $product->stock }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reorder Point</label>
                                <input type="number" name="reorder_point" class="form-control" value="{{ $product->reorder_point }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Low Stock Alert Level</label>
                                <input type="number" name="alert_stock" class="form-control" value="{{ $product->alert_stock }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expiration Date</label>
                                <input type="date" name="expiration_date" class="form-control" 
                                    value="{{ $product->expiration_date ? $product->expiration_date->format('Y-m-d') : '' }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-warning px-4">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection