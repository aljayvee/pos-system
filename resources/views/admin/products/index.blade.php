@extends('admin.layout')

@php
    $barcodeEnabled = \App\Models\Setting::where('key', 'enable_barcode')->value('value') ?? '0';
@endphp

@section('content')
<div class="container-fluid px-14 py-14">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-4 mb-4 gap-3">
        <h1 class="h2 mb-0 text-gray-800">
            <i class="fas fa-box-open text-primary me-2"></i>Products
        </h1>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-success shadow-sm flex-fill flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-csv me-1"></i> Import
            </button>
            <a href="{{ route('products.create') }}" class="btn btn-primary shadow-sm flex-fill flex-md-grow-0">
                <i class="fas fa-plus me-1"></i> Add
            </a>
        </div>
    </div>

    {{-- SEARCH & FILTER --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('products.index') }}" method="GET" class="row g-2">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Search name, SKU..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="filter" class="form-select">
                        <option value="">All Status</option>
                        <option value="low" {{ request('filter') == 'low' ? 'selected' : '' }}>Low Stock</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark flex-fill"><i class="fas fa-filter"></i></button>
                    @if(request('archived'))
                        <a href="{{ route('products.index') }}" class="btn btn-warning flex-fill">Active</a>
                    @else
                        <a href="{{ route('products.index', ['archived' => 1]) }}" class="btn btn-outline-secondary flex-fill"><i class="fas fa-trash-alt"></i></a>
                    @endif
                    @if(request()->anyFilled(['search', 'category', 'filter']))
                        <a href="{{ route('products.index') }}" class="btn btn-light border"><i class="fas fa-undo"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- === DESKTOP TABLE VIEW === --}}
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

    {{-- === MOBILE NATIVE VIEW === --}}
    <div class="d-lg-none">
        @forelse($products as $product)
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body p-3">
                
                {{-- Top Row: Category & Status --}}
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                        {{ $product->category->name ?? 'Uncategorized' }}
                    </span>
                    @if($product->stock == 0) 
                        <span class="badge bg-danger-subtle text-danger rounded-pill">Out of Stock</span>
                    @elseif($product->stock <= $product->reorder_point) 
                        <span class="badge bg-warning-subtle text-warning text-dark-emphasis rounded-pill">Low Stock</span>
                    @endif
                </div>

                {{-- Main Info --}}
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="fw-bold text-dark mb-1 fs-5">{{ $product->name }}</h6>
                        @if($product->sku)
                            <div class="text-muted small"><i class="fas fa-barcode me-1 text-secondary"></i> {{ $product->sku }}</div>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-primary fs-5">₱{{ number_format($product->price, 2) }}</div>
                        <div class="small {{ $product->stock <= $product->reorder_point ? 'text-danger fw-bold' : 'text-muted' }}">
                            {{ $product->stock }} {{ $product->unit }} avail
                        </div>
                    </div>
                </div>

                {{-- Expiry Alert --}}
                @if($product->expiration_date && $product->expiration_date < now()->addDays(30))
                    <div class="mt-2 p-2 rounded bg-danger bg-opacity-10 text-danger small border border-danger border-opacity-25">
                        <i class="fas fa-exclamation-triangle me-1"></i> 
                        Expiring: {{ $product->expiration_date->format('M d, Y') }}
                    </div>
                @endif
            </div>

            {{-- Mobile Action Bar --}}
            <div class="card-footer bg-light p-1 border-top-0">
                <div class="d-flex">
                    @if($barcodeEnabled)
                    <a href="{{ route('products.barcode', $product->id) }}" class="btn btn-link text-dark flex-fill text-decoration-none py-2">
                        <i class="fas fa-barcode"></i>
                    </a>
                    @endif
                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-link text-warning flex-fill text-decoration-none py-2">
                        <i class="fas fa-edit"></i> <span class="small fw-bold">Edit</span>
                    </a>
                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="flex-fill text-center d-grid" 
                          onsubmit="return confirm('Are you sure?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-link text-danger w-100 text-decoration-none py-2">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i><br>No products found.
        </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center justify-content-lg-end">
        {{ $products->links() }}
    </div>
    @include('admin.products.partials.import-modal')
</div>
@endsection