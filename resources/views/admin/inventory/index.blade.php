@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fas fa-warehouse text-primary me-2"></i>Inventory Management
            </h4>
            <p class="text-muted small mb-0">Monitor stock levels, value, and adjustments.</p>
        </div>
        
        {{-- DESKTOP TOOLBAR --}}
        <div class="d-none d-md-flex flex-wrap gap-2">
            <a href="{{ route('purchases.create') }}" class="btn btn-primary shadow-sm rounded-pill fw-bold px-3">
                <i class="fas fa-plus-circle me-1"></i> Restock
            </a>
            <a href="{{ route('inventory.adjust') }}" class="btn btn-warning shadow-sm rounded-pill fw-bold px-3 text-dark">
                <i class="fas fa-sliders-h me-1"></i> Adjust
            </a>
            <div class="btn-group shadow-sm">
                <a href="{{ route('inventory.history') }}" class="btn btn-white border rounded-start-pill fw-bold text-dark">
                    <i class="fas fa-history me-1"></i> History
                </a>
                <a href="{{ route('inventory.export') }}" class="btn btn-success rounded-end-pill fw-bold text-white">
                    <i class="fas fa-file-export me-1"></i> Export
                </a>
            </div>
        </div>

        {{-- MOBILE TOOLBAR (Organized Grid) --}}
        <div class="d-grid d-md-none w-100 gap-2 mt-2" style="grid-template-columns: 1fr 1fr;">
            <a href="{{ route('purchases.create') }}" class="btn btn-primary shadow-sm rounded-3 fw-bold">
                <i class="fas fa-plus-circle me-1"></i> Restock
            </a>
            <a href="{{ route('inventory.adjust') }}" class="btn btn-warning shadow-sm rounded-3 fw-bold text-dark">
                <i class="fas fa-sliders-h me-1"></i> Adjust
            </a>
            <a href="{{ route('inventory.history') }}" class="btn btn-white border shadow-sm rounded-3 fw-bold text-dark">
                <i class="fas fa-history me-1"></i> History
            </a>
            <a href="{{ route('inventory.export') }}" class="btn btn-success shadow-sm rounded-3 fw-bold text-white">
                <i class="fas fa-file-export me-1"></i> Export
            </a>
        </div>
    </div>

    {{-- STATS CARDS (Responsive Grid) --}}
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 rounded-4" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-uppercase fw-bold small mb-0 opacity-75">Total Items</p>
                        <i class="fas fa-box fa-lg opacity-50"></i>
                    </div>
                    <h3 class="fw-bold mb-0">{{ number_format($totalItems) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 rounded-4" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-uppercase fw-bold small mb-0 opacity-75">Total Value</p>
                        <i class="fas fa-coins fa-lg opacity-50"></i>
                    </div>
                    <h3 class="fw-bold mb-0">₱{{ number_format($totalSalesValue, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- SEARCH & FILTER TOOLBAR --}}
    <div class="card mb-4 shadow-sm border-0 rounded-4">
        <div class="card-body p-3">
            <form action="{{ route('inventory.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0 py-2" 
                               placeholder="Search by product name, SKU..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-8 col-md-4">
                    <select name="category" class="form-select bg-light border-0 py-2">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4 col-md-3">
                    <button type="submit" class="btn btn-dark w-100 rounded-pill fw-bold py-2">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- === DESKTOP VIEW TABLE === --}}
    <div class="card shadow-sm mb-4 border-0 d-none d-md-block rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-list me-2 text-primary"></i>Current Stock Levels</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3" style="font-weight: 600;">Product Name</th>
                            <th class="py-3" style="font-weight: 600;">Category</th>
                            <th class="text-end py-3" style="font-weight: 600;">Cost</th>
                            <th class="text-end py-3" style="font-weight: 600;">Price</th>
                            <th class="text-center py-3" style="font-weight: 600;">Stock Level</th>
                            <th class="text-center pe-4 py-3" style="font-weight: 600;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $product->name }}</div>
                                        <div class="small text-muted">{{ $product->sku ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-secondary border rounded-pill fw-normal px-3 py-1">{{ $product->category->name ?? 'Uncategorized' }}</span></td>
                            <td class="text-end text-muted">₱{{ number_format($product->cost ?? 0, 2) }}</td>
                            <td class="text-end fw-bold text-dark">₱{{ number_format($product->price, 2) }}</td>
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="fw-bold {{ $product->stock <= 10 ? 'text-danger' : 'text-dark' }}">{{ $product->stock }}</span>
                                    {{-- Mini Progress Bar --}}
                                    <div class="progress w-75 mt-1" style="height: 4px;">
                                        @php
                                            $percent = min(100, ($product->stock / ($product->reorder_point > 0 ? $product->reorder_point * 3 : 50)) * 100);
                                            $color = $product->stock == 0 ? 'bg-danger' : ($product->stock <= 10 ? 'bg-warning' : 'bg-success');
                                        @endphp
                                        <div class="progress-bar {{ $color }}" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center pe-4">
                                @if($product->stock == 0)
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Out of Stock</span>
                                @elseif($product->stock <= 10)
                                    <span class="badge bg-warning-subtle text-warning text-dark-emphasis rounded-pill px-3">Low Stock</span>
                                @else
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3">In Stock</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No products found matching your criteria.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- === MOBILE NATIVE VIEW (List) === --}}
    <div class="d-md-none">
        @forelse($products as $product)
        <div class="card border-0 shadow-sm mb-3 rounded-4 overflow-hidden">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-light text-secondary border rounded-pill">{{ $product->category->name ?? 'No Category' }}</span>
                    @if($product->stock == 0)
                        <span class="badge bg-danger-subtle text-danger fw-bold"><i class="fas fa-times-circle me-1"></i>Out of Stock</span>
                    @elseif($product->stock <= 10)
                        <span class="badge bg-warning-subtle text-warning text-dark-emphasis fw-bold"><i class="fas fa-exclamation-circle me-1"></i>Low Stock</span>
                    @else
                        <span class="badge bg-success-subtle text-success fw-bold"><i class="fas fa-check-circle me-1"></i>In Stock</span>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fas fa-box text-secondary fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold text-dark mb-0">{{ $product->name }}</h6>
                        <small class="text-muted d-block">{{ $product->sku ?? 'No SKU' }}</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-primary fs-5">₱{{ number_format($product->price, 2) }}</div>
                        <small class="text-muted">Cost: ₱{{ number_format($product->cost ?? 0, 0) }}</small>
                    </div>
                </div>

                <div class="bg-light rounded-3 p-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-bold text-muted text-uppercase" style="font-size: 0.65rem;">Inventory Level</small>
                        <span class="fw-bold {{ $product->stock <= 10 ? 'text-danger' : 'text-dark' }} small">{{ $product->stock }} units</span>
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
            <div class="mb-3">
                <i class="fas fa-search fa-3x text-light-gray opacity-25"></i>
            </div>
            <h6 class="fw-bold text-secondary">No products found</h6>
            <p class="small">Try adjusting filters or search terms.</p>
        </div>
        @endforelse
    </div>

    @if($products->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection