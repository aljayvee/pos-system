@extends('admin.layout')

@php
    $barcodeEnabled = \App\Models\Setting::where('key', 'enable_barcode')->value('value') ?? '0';
@endphp

@section('content')
<div class="container-fluid px-14 py-14">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-4 mb-4 gap-3">
        <h1 class="h2 mb-0 text-gray-800">
            <i class="fas fa-box-open text-primary me-2"></i>Product Management
        </h1>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-success shadow-sm flex-fill flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-csv me-1"></i> Import
            </button>
            <a href="{{ route('products.create') }}" class="btn btn-primary shadow-sm flex-fill flex-md-grow-0">
                <i class="fas fa-plus me-1"></i> Add Product
            </a>
        </div>
    </div>

    {{-- SEARCH & FILTER TOOLBAR --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('products.index') }}" method="GET" class="row g-2">
                
                {{-- Search --}}
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Search name, SKU..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Filters --}}
                <div class="col-6 col-md-3">
                    <select name="category" class="form-select form-select-sm h-100">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="filter" class="form-select form-select-sm h-100">
                        <option value="">All Status</option>
                        <option value="low" {{ request('filter') == 'low' ? 'selected' : '' }}>Low Stock</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark flex-fill">
                        <i class="fas fa-filter"></i>
                    </button>
                    @if(request('archived'))
                        <a href="{{ route('products.index') }}" class="btn btn-warning flex-fill">Active</a>
                    @else
                        <a href="{{ route('products.index', ['archived' => 1]) }}" class="btn btn-outline-secondary flex-fill" title="Trash">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    @endif
                    @if(request()->anyFilled(['search', 'category', 'filter']))
                        <a href="{{ route('products.index') }}" class="btn btn-light border" title="Reset">
                            <i class="fas fa-undo"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- === DESKTOP TABLE VIEW (Visible on Large Screens) === --}}
    <div class="card shadow-sm border-0 d-none d-lg-block mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Product Name</th>
                            <th class="py-3">Category</th>
                            <th class="text-end py-3">Price</th>
                            <th class="text-center py-3">Stock</th>
                            <th class="text-center py-3">Status</th>
                            <th class="text-end pe-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $product->name }}</div>
                                @if($product->sku) <small class="text-muted">{{ $product->sku }}</small> @endif
                            </td>
                            <td><span class="badge bg-light text-secondary border">{{ $product->category->name ?? 'Uncategorized' }}</span></td>
                            <td class="text-end fw-bold">₱{{ number_format($product->price, 2) }}</td>
                            <td class="text-center">
                                <span class="{{ $product->stock <= $product->reorder_point ? 'text-danger fw-bold' : '' }}">{{ $product->stock }}</span>
                                <small class="text-muted">{{ $product->unit }}</small>
                            </td>
                            <td class="text-center">
                                @if($product->stock == 0) <span class="badge bg-danger-subtle text-danger">Out of Stock</span>
                                @elseif($product->stock <= $product->reorder_point) <span class="badge bg-warning-subtle text-warning text-dark-emphasis">Low Stock</span>
                                @else <span class="badge bg-success-subtle text-success">Active</span>
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
    </div>

    {{-- === MOBILE/PHABLET CARD LIST VIEW (Visible on Small/Medium Screens) === --}}
    <div class="d-lg-none">
        <div class="row g-3">
            @forelse($products as $product)
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-0 h-100 {{ $product->stock <= $product->reorder_point ? 'border-start border-4 border-warning' : '' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge bg-light text-secondary border">{{ $product->category->name ?? 'Uncategorized' }}</span>
                            @if($product->stock == 0) 
                                <span class="badge bg-danger-subtle text-danger">Out of Stock</span>
                            @elseif($product->stock <= $product->reorder_point) 
                                <span class="badge bg-warning-subtle text-warning text-dark-emphasis">Low Stock</span>
                            @else 
                                <span class="badge bg-success-subtle text-success">Good</span>
                            @endif
                        </div>
                        
                        <h5 class="fw-bold text-dark mb-0">{{ $product->name }}</h5>
                        @if($product->sku)
                                <small class="text-muted d-block mb-2"><i class="fas fa-barcode me-1"></i>{{ $product->sku }}</small>
                           
                        @endif

                        <div class="d-flex justify-content-between align-items-center mt-3 bg-light rounded p-2">
                            <div>
                                <small class="text-muted text-uppercase d-block" style="font-size: 0.7rem;">Price</small>
                                <span class="fw-bold text-primary">₱{{ number_format($product->price, 2) }}</span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted text-uppercase d-block" style="font-size: 0.7rem;">Stock</small>
                                <span class="fw-bold {{ $product->stock <= $product->reorder_point ? 'text-danger' : 'text-dark' }}">
                                    {{ $product->stock }} <!--<small class="fw-normal text-muted">{{ $product->unit }}</small>-->
                                </span>
                            </div>
                        </div>

                        {{-- Expiry Warning for Mobile --}}
                        @if($product->expiration_date && $product->expiration_date < now()->addDays(30))
                            <div class="alert alert-danger py-1 px-2 mt-2 mb-0 small">
                                <i class="fas fa-exclamation-triangle me-1"></i> 
                                Exp: {{ $product->expiration_date->format('M d, Y') }}
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                        <div class="d-grid gap-2 d-flex justify-content-end">
                            @include('admin.products.partials.actions', ['product' => $product, 'mobile' => true])
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i><br>
                No products found.
            </div>
            @endforelse
        </div>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-4 d-flex justify-content-center justify-content-lg-end">
        {{ $products->links() }}
    </div>

    @include('admin.products.partials.import-modal')

</div>
@endsection