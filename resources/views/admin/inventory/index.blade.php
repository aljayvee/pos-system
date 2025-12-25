@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    {{-- MOBILE HEADER --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm px-3 py-3 d-flex align-items-center justify-content-between z-3 mb-3" style="top: 0;">
        <div style="width: 40px;"></div> {{-- Spacer --}}
        <h6 class="m-0 fw-bold text-dark">Inventory</h6>
        <div style="width: 40px;"></div> {{-- Spacer --}}
    </div>

    {{-- DESKTOP HEADER --}}
    <div class="d-none d-lg-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fas fa-warehouse text-primary me-2"></i>Inventory Management
            </h4>
            <p class="text-muted small mb-0">Monitor stock levels, value, and adjustments.</p>
        </div>
        
        {{-- DESKTOP TOOLBAR --}}
        <div class="d-flex flex-wrap gap-2">
            @if(auth()->user()->role !== 'auditor')
            <a href="{{ route('purchases.create') }}" class="btn btn-primary shadow-sm rounded-pill fw-bold px-3">
                <i class="fas fa-plus-circle me-1"></i> Restock
            </a>
            <a href="{{ route('inventory.adjust') }}" class="btn btn-warning shadow-sm rounded-pill fw-bold px-3 text-dark">
                <i class="fas fa-sliders-h me-1"></i> Adjust
            </a>
            @endif
            <div class="btn-group shadow-sm">
                <a href="{{ route('inventory.history') }}" class="btn btn-white border rounded-start-pill fw-bold text-dark">
                    <i class="fas fa-history me-1"></i> History
                </a>
                <a href="{{ route('inventory.export') }}" class="btn btn-success rounded-end-pill fw-bold text-white">
                    <i class="fas fa-file-export me-1"></i> Export
                </a>
            </div>
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
                               placeholder="Search product..." value="{{ request('search') }}">
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
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- === DESKTOP VIEW TABLE === --}}
    <div class="card shadow-sm mb-4 border-0 d-none d-lg-block rounded-4 overflow-hidden">
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
    <div class="d-lg-none card shadow-sm border-0 rounded-4 overflow-hidden mb-5">
        <ul class="list-group list-group-flush">
            @forelse($products as $product)
            <li class="list-group-item p-3 border-bottom-0 hover-bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                        <i class="fas fa-box text-secondary fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="fw-bold text-dark mb-0">{{ $product->name }}</h6>
                            <span class="fw-bold text-primary">₱{{ number_format($product->price, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <small class="text-muted d-block">{{ $product->category->name ?? 'No Cat' }}</small>
                                <small class="text-muted text-uppercase" style="font-size: 0.65rem;">{{ $product->sku ?? '' }}</small>
                            </div>
                            <div class="text-end">
                                <small class="fw-bold {{ $product->stock <= 10 ? 'text-danger' : 'text-dark' }}">{{ $product->stock }} <span class="text-muted fw-normal">units</span></small>
                                <div class="progress mt-1" style="height: 4px; width: 60px;">
                                    @php
                                        $percent = min(100, ($product->stock / ($product->reorder_point > 0 ? $product->reorder_point * 3 : 50)) * 100);
                                        $color = $product->stock == 0 ? 'bg-danger' : ($product->stock <= 10 ? 'bg-warning' : 'bg-success');
                                    @endphp
                                    <div class="progress-bar {{ $color }}" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-search fa-3x mb-3 text-light-gray opacity-25"></i>
                <h6 class="fw-bold text-secondary">No products found</h6>
                <p class="small">Try adjusting filters.</p>
            </div>
            @endforelse
        </ul>
    </div>

    {{-- MOBILE FAB (Trigger Action Sheet) --}}
    <div class="d-lg-none position-fixed end-0 p-3 z-3" style="bottom: 80px;">
        <button class="btn btn-primary shadow-lg rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;" data-bs-toggle="modal" data-bs-target="#inventoryActionSheet">
            <i class="fas fa-plus fa-lg"></i>
        </button>
    </div>

    {{-- MOBILE ACTION SHEET --}}
    <div class="modal fade" id="inventoryActionSheet" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable fixed-bottom m-0" style="max-width: 100%;">
            <div class="modal-content rounded-top-4 border-0 shadow-lg" style="max-height: 80vh;">
                <div class="modal-header border-bottom-0 pb-0 justify-content-center">
                    <div class="bg-secondary bg-opacity-25 rounded-pill" style="width: 40px; height: 5px;"></div>
                </div>
                <div class="modal-body pt-4 pb-4">
                    <h5 class="fw-bold text-center mb-4">Inventory Actions</h5>
                    
                    <div class="d-grid gap-3">
                        @if(auth()->user()->role !== 'auditor')
                        <a href="{{ route('purchases.create') }}" class="btn btn-light shadow-sm text-start p-3 rounded-4 d-flex align-items-center">
                            <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div>
                                <span class="d-block fw-bold text-dark">Restock Products</span>
                                <small class="text-muted">Create a new purchase order</small>
                            </div>
                        </a>

                        <a href="{{ route('inventory.adjust') }}" class="btn btn-light shadow-sm text-start p-3 rounded-4 d-flex align-items-center">
                            <div class="bg-warning text-dark rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                            <div>
                                <span class="d-block fw-bold text-dark">Adjust Stock</span>
                                <small class="text-muted">Correct inventory levels</small>
                            </div>
                        </a>
                        @endif

                        <a href="{{ route('inventory.history') }}" class="btn btn-light shadow-sm text-start p-3 rounded-4 d-flex align-items-center">
                            <div class="bg-secondary text-white rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fas fa-history"></i>
                            </div>
                            <div>
                                <span class="d-block fw-bold text-dark">View History</span>
                                <small class="text-muted">See adjustment logs</small>
                            </div>
                        </a>

                        <a href="{{ route('inventory.export') }}" class="btn btn-light shadow-sm text-start p-3 rounded-4 d-flex align-items-center">
                            <div class="bg-success text-white rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fas fa-file-export"></i>
                            </div>
                            <div>
                                <span class="d-block fw-bold text-dark">Export Data</span>
                                <small class="text-muted">Download CSV report</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($products->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection