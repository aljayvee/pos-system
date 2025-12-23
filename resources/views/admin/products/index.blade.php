@extends('admin.layout')

@php
    $barcodeEnabled = \App\Models\Setting::where('key', 'enable_barcode')->value('value') ?? '0';
@endphp

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark m-0 tracking-tight">Product Inventory</h3>
            <p class="text-muted small m-0">Manage stock, prices, and categories.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-white border shadow-sm rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import me-2 text-secondary"></i>Import CSV
            </button>
            <a href="{{ route('products.create') }}" class="btn btn-primary shadow-lg rounded-pill px-4 fw-bold">
                <i class="fas fa-plus-circle me-2"></i>Add Product
            </a>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative h-100">
                <div class="card-body p-3 position-relative z-1">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex flex-column">
                            <span class="text-muted small text-uppercase fw-bold ls-1">Total Items</span>
                            <h2 class="mb-0 fw-bold text-dark mt-1">{{ \App\Models\Product::count() }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2">
                            <i class="fas fa-box fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative h-100">
                <div class="card-body p-3 position-relative z-1">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex flex-column">
                            <span class="text-muted small text-uppercase fw-bold ls-1">Low Stock</span>
                            <h2 class="mb-0 fw-bold text-danger mt-1">{{ \App\Models\Product::whereRaw('stock <= reorder_point')->count() }}</h2>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-2">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TOOLBAR --}}
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('products.index') }}" method="GET" class="row g-2 align-items-center">
                {{-- Search --}}
                <div class="col-12 col-md-4">
                    <div class="input-group shadow-sm rounded-pill overflow-hidden border-0">
                        <span class="input-group-text bg-light border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-0 bg-light" placeholder="Search product..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Filters --}}
                <div class="col-6 col-md-3">
                    <select name="category" class="form-select border-0 bg-light shadow-sm rounded-pill" style="cursor: pointer;">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="filter" class="form-select border-0 bg-light shadow-sm rounded-pill" style="cursor: pointer;">
                        <option value="">All Status</option>
                        <option value="low" {{ request('filter') == 'low' ? 'selected' : '' }}>Low Stock</option>
                    </select>
                </div>

                {{-- Actions --}}
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark rounded-pill px-4 flex-fill fw-bold shadow-sm">Filter</button>
                    
                    @if(request('archived'))
                        <a href="{{ route('products.index') }}" class="btn btn-warning rounded-pill flex-fill fw-bold shadow-sm">Active</a>
                    @else
                        <a href="{{ route('products.index', ['archived' => 1]) }}" class="btn btn-outline-secondary rounded-pill flex-fill shadow-sm" title="View Archived">
                            <i class="fas fa-archive"></i>
                        </a>
                    @endif

                    @if(request()->anyFilled(['search', 'category', 'filter']))
                        <a href="{{ route('products.index') }}" class="btn btn-light border rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;" title="Reset">
                            <i class="fas fa-undo text-secondary"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- DESKTOP TABLE --}}
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden d-none d-lg-block mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Product Name</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold">Category</th>
                        <th class="text-end py-3 text-secondary text-uppercase small fw-bold">Price</th>
                        <th class="text-center py-3 text-secondary text-uppercase small fw-bold">Stock</th>
                        <th class="text-center py-3 text-secondary text-uppercase small fw-bold">Status</th>
                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; flex-shrink: 0;">
                                    {{ substr($product->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $product->name }}</div>
                                    @if($product->sku) <span class="badge bg-light text-secondary border fw-normal">{{ $product->sku }}</span> @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-white border text-secondary fw-normal px-3 py-2 rounded-pill shadow-sm">
                                {{ $product->category->name ?? 'Uncategorized' }}
                            </span>
                        </td>
                        <td class="text-end fw-bold text-dark">₱{{ number_format($product->price, 2) }}</td>
                        <td class="text-center">
                            <span class="{{ $product->stock <= $product->reorder_point ? 'text-danger fw-bold' : 'text-dark fw-bold' }}">{{ $product->stock }}</span>
                            <small class="text-muted d-block x-small">{{ $product->unit }}</small>
                        </td>
                        <td class="text-center">
                            @if($product->stock == 0) <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Out of Stock</span>
                            @elseif($product->stock <= $product->reorder_point) <span class="badge bg-warning-subtle text-warning border border-warning-subtle text-dark-emphasis rounded-pill px-3">Low Stock</span>
                            @else <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Active</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            @include('admin.products.partials.actions', ['product' => $product])
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MOBILE CARD VIEW --}}
    <div class="d-lg-none bg-light p-2">
        @forelse($products as $product)
        <div class="card shadow-sm border-0 mb-3 rounded-4 overflow-hidden">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-light text-secondary border fw-normal shadow-sm">
                        {{ $product->category->name ?? 'Uncategorized' }}
                    </span>
                    @if($product->stock == 0) <span class="badge bg-danger text-white rounded-pill">Out</span>
                    @elseif($product->stock <= $product->reorder_point) <span class="badge bg-warning text-dark rounded-pill">Low</span>
                    @else <span class="badge bg-success text-white rounded-pill">Active</span>
                    @endif
                </div>

                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold text-dark mb-1">{{ $product->name }}</h6>
                        @if($product->sku) <div class="text-muted small"><i class="fas fa-barcode me-1"></i> {{ $product->sku }}</div> @endif
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-primary fs-5">₱{{ number_format($product->price, 2) }}</div>
                        <div class="small {{ $product->stock <= $product->reorder_point ? 'text-danger fw-bold' : 'text-muted' }}">{{ $product->stock }} {{ $product->unit }}</div>
                    </div>
                </div>

                @if($product->expiration_date && $product->expiration_date < now()->addDays(30))
                    <div class="p-2 rounded bg-danger bg-opacity-10 text-danger small border border-danger border-opacity-25 mb-3">
                        <i class="fas fa-exclamation-triangle me-1"></i> Expiring: {{ $product->expiration_date->format('M d, Y') }}
                    </div>
                @endif

                <div class="d-flex gap-2 border-top pt-3">
                    @if($barcodeEnabled)
                    <a href="{{ route('products.barcode', $product->id) }}" class="btn btn-light border shadow-sm flex-fill fw-bold text-secondary py-2 rounded-3">
                        <i class="fas fa-print"></i>
                    </a>
                    @endif
                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning flex-fill fw-bold text-dark py-2 shadow-sm rounded-3">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    
                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="flex-fill" onsubmit="return confirm('Delete?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger w-100 fw-bold text-white py-2 shadow-sm rounded-3">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">No products found.</div>
        @endforelse
    </div>

    @if($products->hasPages())
    <div class="d-flex justify-content-center mt-4">{{ $products->links() }}</div>
    @endif

    @include('admin.products.partials.import-modal')

</div>
@endsection
