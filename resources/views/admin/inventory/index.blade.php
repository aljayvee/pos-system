@extends('admin.layout')

@section('content')
<div class="container-fluid px-14 py-14">
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-4 mb-4 gap-3">
        <h1 class="h2 mb-0 text-gray-800">
            <i class="fas fa-warehouse text-primary me-2"></i>Inventory
        </h1>
        
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('purchases.create') }}" class="btn btn-primary shadow-sm flex-fill flex-md-grow-0">
                <i class="fas fa-plus-circle me-1"></i> Restock
            </a>
            <a href="{{ route('inventory.adjust') }}" class="btn btn-warning shadow-sm flex-fill flex-md-grow-0">
                <i class="fas fa-sliders-h me-1"></i> Adjust
            </a>
            <div class="btn-group shadow-sm flex-fill flex-md-grow-0">
                <a href="{{ route('inventory.history') }}" class="btn btn-secondary">
                    <i class="fas fa-history me-1"></i> History
                </a>
            </div>
            <div class="btn-group shadow-sm flex-fill flex-md-grow-0">
             <a href="{{ route('inventory.export') }}" class="btn btn-success">
                    <i class="fas fa-file-export me-1"></i> Export
                </a>
            </div>
        </div>
    </div>

    {{-- STATS CARDS (Responsive Grid) --}}
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <p class="text-uppercase text-muted fw-bold small mb-1" style="font-size: 0.7rem;">Items</p>
                    <h5 class="fw-bold mb-0 text-primary">{{ number_format($totalItems) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <p class="text-uppercase text-muted fw-bold small mb-1" style="font-size: 0.7rem;">Value</p>
                    <h5 class="fw-bold mb-0 text-success">₱{{ number_format($totalSalesValue, 2) }}</h5>
                </div>
            </div>
        </div>
    </div>

    {{-- SEARCH & FILTER --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-3">
            <form action="{{ route('inventory.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Search product..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-8 col-md-4">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4 col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- === DESKTOP VIEW TABLE (Visible on Medium screens and up) === --}}
    <div class="card shadow-sm mb-4 border-0 d-none d-md-block">
        <div class="card-header bg-white py-3">
            <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-list me-2"></i>Current Stock Levels</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0 text-nowrap">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Product Name</th>
                            <th>Category</th>
                            <th class="text-end">Cost</th>
                            <th class="text-end">Price</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $product->name }}</td>
                            <td><span class="badge bg-light text-secondary border">{{ $product->category->name ?? 'Uncategorized' }}</span></td>
                            <td class="text-end text-muted small">₱{{ number_format($product->cost ?? 0, 2) }}</td>
                            <td class="text-end fw-bold">₱{{ number_format($product->price, 2) }}</td>
                            <td class="text-center">
                                <span class="badge rounded-pill {{ $product->stock <= 10 ? 'bg-danger' : 'bg-primary' }} px-3">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                @if($product->stock == 0)
                                    <span class="badge bg-danger-subtle text-danger border border-danger">Out of Stock</span>
                                @elseif($product->stock <= 10)
                                    <span class="badge bg-warning-subtle text-warning border border-warning">Low Stock</span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success">In Stock</span>
                                @endif
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

    {{-- === MOBILE NATIVE VIEW (Cards) === --}}
    <div class="d-md-none">
        @forelse($products as $product)
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="badge bg-light text-secondary border rounded-pill">{{ $product->category->name ?? 'No Category' }}</span>
                    @if($product->stock == 0)
                        <span class="badge bg-danger-subtle text-danger">Out of Stock</span>
                    @elseif($product->stock <= 10)
                        <span class="badge bg-warning-subtle text-warning text-dark-emphasis">Low Stock</span>
                    @else
                        <span class="badge bg-success-subtle text-success">In Stock</span>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="flex-grow-1">
                        <h6 class="fw-bold text-dark mb-0 fs-5">{{ $product->name }}</h6>
                        <small class="text-muted">Cost: ₱{{ number_format($product->cost ?? 0, 2) }}</small>
                    </div>
                    <div class="text-end">
                        <h5 class="fw-bold text-primary mb-0">₱{{ number_format($product->price, 2) }}</h5>
                    </div>
                </div>

                {{-- Stock Progress Bar --}}
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">Stock Level</small>
                        <span class="fw-bold {{ $product->stock <= 10 ? 'text-danger' : 'text-dark' }}">{{ $product->stock }} units</span>
                    </div>
                    <div class="progress" style="height: 6px; border-radius: 3px;">
                        @php
                            $percent = min(100, ($product->stock / ($product->reorder_point > 0 ? $product->reorder_point * 3 : 50)) * 100);
                            $color = $product->stock == 0 ? 'bg-danger' : ($product->stock <= 10 ? 'bg-warning' : 'bg-success');
                        @endphp
                        <div class="progress-bar {{ $color }}" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-box-open fa-3x mb-3 text-gray-300"></i><br>
            No products found.
        </div>
        @endforelse
    </div>

    @if($products->hasPages())
    <div class="card-footer bg-white d-flex justify-content-center justify-content-md-end py-3">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection