@extends('admin.layout')

@php
    $barcodeEnabled = \App\Models\Setting::where('key', 'enable_barcode')->value('value') ?? '0';
@endphp

@section('content')
<div class="container-fluid px-2 px-md-4 py-4" style="max-width: 1400px;">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Product Inventory</h4>
            <p class="text-muted small mb-0">Manage your stock, prices, and categories.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-light bg-white border shadow-sm flex-fill flex-md-grow-0 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import text-secondary"></i> <span>Import CSV</span>
            </button>
            <a href="{{ route('products.create') }}" class="btn btn-primary shadow-sm flex-fill flex-md-grow-0 d-flex align-items-center gap-2 fw-bold px-4">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
    </div>

    {{-- TOOLBAR: SEARCH & FILTER --}}
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('products.index') }}" method="GET" class="row g-2 align-items-center">
                
                {{-- Search --}}
                <div class="col-12 col-md-4">
                    <div class="position-relative">
                        <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-0 ps-5 py-2" 
                               placeholder="Search product name, SKU..." value="{{ request('search') }}" style="border-radius: 2rem;">
                    </div>
                </div>

                {{-- Filters --}}
                <div class="col-6 col-md-3">
                    <select name="category" class="form-select border-0 bg-light py-2" style="border-radius: 2rem; cursor: pointer;">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="filter" class="form-select border-0 bg-light py-2" style="border-radius: 2rem; cursor: pointer;">
                        <option value="">All Status</option>
                        <option value="low" {{ request('filter') == 'low' ? 'selected' : '' }}>Low Stock</option>
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark rounded-pill px-4 flex-fill"><i class="fas fa-filter me-1"></i> Filter</button>
                    
                    @if(request('archived'))
                        <a href="{{ route('products.index') }}" class="btn btn-warning rounded-pill flex-fill">Active</a>
                    @else
                        <a href="{{ route('products.index', ['archived' => 1]) }}" class="btn btn-outline-secondary rounded-pill flex-fill" title="View Archived">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    @endif

                    @if(request()->anyFilled(['search', 'category', 'filter']))
                        <a href="{{ route('products.index') }}" class="btn btn-light border rounded-pill px-3" title="Clear Filters">
                            <i class="fas fa-undo text-muted"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- === DESKTOP TABLE VIEW === --}}
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden d-none d-lg-block mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-secondary text-uppercase x-small fw-bold">Product Name</th>
                            <th class="py-3 text-secondary text-uppercase x-small fw-bold">Category</th>
                            <th class="text-end py-3 text-secondary text-uppercase x-small fw-bold">Price</th>
                            <th class="text-center py-3 text-secondary text-uppercase x-small fw-bold">Stock</th>
                            <th class="text-center py-3 text-secondary text-uppercase x-small fw-bold">Status</th>
                            <th class="text-end pe-4 py-3 text-secondary text-uppercase x-small fw-bold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr class="transition-all hover-bg-light">
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 40px;">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $product->name }}</div>
                                        @if($product->sku) <span class="badge bg-light text-secondary border fw-normal">{{ $product->sku }}</span> @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-normal px-3 py-2 rounded-pill">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="text-end fw-bold text-dark fs-6">₱{{ number_format($product->price, 2) }}</td>
                            <td class="text-center">
                                <span class="{{ $product->stock <= $product->reorder_point ? 'text-danger fw-bold' : 'text-dark fw-bold' }} fs-6">{{ $product->stock }}</span>
                                <small class="text-muted d-block x-small">{{ $product->unit }}</small>
                            </td>
                            <td class="text-center">
                                @if($product->stock == 0) <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Out of Stock</span>
                                @elseif($product->stock <= $product->reorder_point) <span class="badge bg-warning-subtle text-warning text-dark-emphasis rounded-pill px-3">Low Stock</span>
                                @else <span class="badge bg-success-subtle text-success rounded-pill px-3">Active</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @include('admin.products.partials.actions', ['product' => $product])
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-box-open fa-3x mb-3 text-light"></i>
                            <p class="mb-0">No products found matching your search.</p>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- === MOBILE CARD VIEW === --}}
    <div class="d-lg-none">
        @forelse($products as $product)
        <div class="card shadow-sm border-0 mb-3 rounded-4 overflow-hidden">
            <div class="card-body p-3">
                
                {{-- Top: Category & Status Badge --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-light text-secondary border fw-normal">
                        {{ $product->category->name ?? 'Uncategorized' }}
                    </span>
                    @if($product->stock == 0) 
                        <span class="badge bg-danger-subtle text-danger rounded-pill">Out of Stock</span>
                    @elseif($product->stock <= $product->reorder_point) 
                        <span class="badge bg-warning-subtle text-warning text-dark-emphasis rounded-pill">Low Stock</span>
                    @else
                        <span class="text-success small fw-bold"><i class="fas fa-check-circle me-1"></i>Active</span>
                    @endif
                </div>

                {{-- Middle: Name & Price --}}
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold text-dark mb-1">{{ $product->name }}</h6>
                        @if($product->sku)
                            <div class="text-muted x-small"><i class="fas fa-barcode me-1 text-secondary"></i> {{ $product->sku }}</div>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-primary fs-5">₱{{ number_format($product->price, 2) }}</div>
                        <div class="small {{ $product->stock <= $product->reorder_point ? 'text-danger fw-bold' : 'text-muted' }}">
                            {{ $product->stock }} {{ $product->unit }} left
                        </div>
                    </div>
                </div>

                {{-- Expiry Alert --}}
                @if($product->expiration_date && $product->expiration_date < now()->addDays(30))
                    <div class="p-2 rounded bg-danger bg-opacity-10 text-danger x-small border border-danger border-opacity-25 mb-3">
                        <i class="fas fa-exclamation-triangle me-1"></i> 
                        Expiring: {{ $product->expiration_date->format('M d, Y') }}
                    </div>
                @endif

                {{-- Actions Grid --}}
                <div class="d-flex gap-2 border-top pt-3">
                    @if($barcodeEnabled)
                    <a href="{{ route('products.barcode', $product->id) }}" class="btn btn-secondary btn-sm flex-fill text-white py-2 shadow-sm">
                        <i class="fas fa-barcode"></i>
                    </a>
                    @endif
                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning btn-sm flex-fill fw-bold text-dark py-2 shadow-sm">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    
                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="flex-fill text-center d-grid" 
                          onsubmit="return confirm('Are you sure?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm w-100 fw-bold text-white py-2 shadow-sm">
                            <i class="fas fa-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-search fa-3x mb-3 text-light"></i><br>No products found.
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-4 d-flex justify-content-center justify-content-lg-end">
        {{ $products->links() }}
    </div>

    @include('admin.products.partials.import-modal')

</div>

<style>
.x-small { font-size: 0.75rem; }
.transition-all { transition: all 0.2s ease-in-out; }
.hover-bg-light:hover { background-color: #f8f9fa !important; }
</style>
@endsection