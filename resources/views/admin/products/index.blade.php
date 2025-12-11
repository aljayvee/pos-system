@extends('admin.layout')
@php
    // Fetch Setting
    $barcodeEnabled = \App\Models\Setting::where('key', 'enable_barcode')->value('value') ?? '0';
@endphp
@section('content')

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h1>Products</h1>
        <div>
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-csv"></i> Import CSV
            </button>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
    </div>

    {{-- SEARCH & FILTER TOOLBAR --}}
    <div class="card bg-light border-0 mb-4">
        <div class="card-body py-3">
            <form action="{{ route('products.index') }}" method="GET" class="row g-2 align-items-center">
                
                {{-- Search Input --}}
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Search name, barcode/SKU..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Category Filter --}}
                <div class="col-md-3">
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Categories --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Quick Filter --}}
                <div class="col-md-3">
                    <select name="filter" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Status --</option>
                        <option value="low" {{ request('filter') == 'low' ? 'selected' : '' }}>Low Stock Only</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100">Filter</button>
                    @if(request()->anyFilled(['search', 'category', 'filter']))
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary" title="Reset">
                            <i class="fas fa-undo"></i>
                        </a>
                    @endif
                </div>

            </form>
        </div>
    </div>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Product Inventory</h2>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> Add New Product
        </a>
    </div>

    @if(session('success')) 
        <div class="alert alert-success">{{ session('success') }}</div> 
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>SKU</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Cost</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>{{ $product->sku ?? '-' }}</td>
                        <td>
                            <div class="fw-bold">{{ $product->name }}</div>
                            <small class="text-muted">{{ ucfirst($product->unit) }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $product->category->name ?? 'None' }}</span>
                        </td>
                        <td>₱{{ number_format($product->price, 2) }}</td>
                        <td>{{ $product->cost ? '₱'.number_format($product->cost, 2) : '-' }}</td>
                        <td>
                            @if($product->stock <= $product->alert_stock)
                                <span class="text-danger fw-bold">{{ $product->stock }} (Low)</span>
                            @else
                                <span class="text-success">{{ $product->stock }}</span>
                            @endif
                        </td>
                        <td>
                            {{-- ... Edit Button ... --}}

                            {{-- BARCODE BUTTON (Condition: Feature ON + Product has SKU) --}}
                            @if($barcodeEnabled == '1' && $product->sku)
                                <a href="{{ route('products.barcode', $product->id) }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-dark" 
                                   title="Print Barcode">
                                    <i class="fas fa-barcode"></i>
                                </a>
                            @endif

                            {{-- ... Delete Button ... --}}
                        </td>
                        <td>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i><br>
                                No products found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- INSERT THIS MODAL AT THE BOTTOM OF THE FILE --}}
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Products via CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        
                        <div class="mb-3">
                            <label class="form-label">Select CSV File</label>
                            <input type="file" name="csv_file" class="form-control" required accept=".csv">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Upload & Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@endsection