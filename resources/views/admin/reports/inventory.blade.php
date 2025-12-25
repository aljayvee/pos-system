@extends('admin.layout')

@section('content')
<div class="container-fluid px-0 px-md-4 py-0 py-md-4">
    {{-- MOBILE HEADER & NAV --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm z-3">
        <div class="px-3 py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-boxes text-warning me-2"></i>Inventory</h6>
        </div>
        
        {{-- Horizontal Scrollable Nav --}}
        <div class="d-flex overflow-auto px-3 pb-3 gap-2 no-scrollbar">
            <a href="{{ route('reports.index') }}" class="btn btn-light border rounded-pill px-4 text-secondary flex-shrink-0">Sales</a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-primary rounded-pill shadow-sm fw-bold px-4 flex-shrink-0">Inventory</a>
            <a href="{{ route('reports.credits') }}" class="btn btn-light border rounded-pill px-4 text-secondary flex-shrink-0">Credits</a>
            <a href="{{ route('reports.forecast') }}" class="btn btn-light border rounded-pill px-4 text-secondary flex-shrink-0">Forecast</a>
        </div>
    </div>

    {{-- DESKTOP HEADER --}}
    <div class="d-none d-lg-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="fas fa-boxes text-warning me-2"></i>Inventory Report</h4>
            <p class="text-muted small mb-0">Detailed stock balance and valuation.</p>
        </div>
        
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('reports.index') }}" class="btn btn-white border shadow-sm flex-fill flex-xl-grow-0 rounded-pill px-4 text-secondary hover-primary single-click-link">Sales</a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-primary shadow-sm flex-fill flex-xl-grow-0 rounded-pill fw-bold px-4 single-click-link">Inventory</a>
            <a href="{{ route('reports.credits') }}" class="btn btn-white border shadow-sm flex-fill flex-xl-grow-0 rounded-pill px-4 text-secondary hover-primary single-click-link">Credits</a>
            <a href="{{ route('reports.forecast') }}" class="btn btn-white border shadow-sm flex-fill flex-xl-grow-0 rounded-pill px-4 text-secondary hover-primary single-click-link">Forecast</a>
        </div>
    </div>

    <div class="px-3 px-md-0 pt-3 pt-md-0">
        {{-- METRICS --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="card text-white shadow-sm border-0 rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #212529 0%, #343a40 100%);">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50 text-uppercase fw-bold">Stock Value (SRP)</small>
                            <h2 class="fw-bold mb-0">₱{{ number_format($totalValue, 2) }}</h2>
                            <span class="badge bg-white bg-opacity-10 text-white border border-secondary mt-2">Potential Revenue</span>
                        </div>
                        <div class="bg-white bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-tags fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card text-dark shadow-sm border-0 rounded-4 overflow-hidden" style="background-color: #e9ecef;">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Total Cost (Capital)</small>
                            <h2 class="fw-bold mb-0">₱{{ number_format($totalCost, 2) }}</h2>
                            <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle mt-2">Investment Locked</span>
                        </div>
                        <div class="bg-white rounded-circle p-3 shadow-sm">
                            <i class="fas fa-wallet fa-2x text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STOCK LEVEL TABLE --}}
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom border-light">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-cubes me-2 text-primary"></i>Stock Levels</h5>
                <a href="{{ route('reports.export', ['report_type' => 'inventory']) }}" class="btn btn-sm btn-success shadow-sm rounded-pill px-3">
                    <i class="fas fa-download me-1"></i> CSV
                </a>
            </div>

            {{-- Desktop View --}}
            <div class="d-none d-lg-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small text-secondary fw-bold">
                            <tr>
                                <th class="ps-4 py-3">Product</th>
                                <th class="py-3">Category</th>
                                <th class="text-center py-3">Stock</th>
                                <th class="text-end py-3">Cost</th>
                                <th class="text-end py-3">Price</th>
                                <th class="text-center pe-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventory as $item)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark">{{ $item->name }}</span>
                                    <div class="text-muted small">{{ $item->sku }}</div>
                                </td>
                                <td><span class="badge bg-light text-secondary border rounded-pill">{{ $item->category->name ?? '-' }}</span></td>
                                <td class="text-center">
                                    <span class="fw-bold {{ $item->current_stock <= $item->reorder_point ? 'text-danger' : 'text-dark' }}">
                                        {{ $item->current_stock }}
                                    </span>
                                </td>
                                <td class="text-end text-muted">₱{{ number_format($item->cost, 2) }}</td>
                                <td class="text-end fw-bold text-primary">₱{{ number_format($item->price, 2) }}</td>
                                <td class="text-center pe-4">
                                    @if($item->current_stock == 0) 
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Out of Stock</span>
                                    @elseif($item->current_stock <= $item->reorder_point) 
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 text-dark-emphasis">Low Stock</span>
                                    @else 
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Good</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile List (Standard Native List + Action Sheet) --}}
            <div class="d-lg-none menu-grid">
                <div class="list-group list-group-flush">
                    @forelse($inventory as $item)
                    <div class="list-group-item p-3 border-bottom-0 hover-bg-light" 
                         data-bs-toggle="modal" data-bs-target="#inventoryActionSheet-{{ $item->id }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark">{{ $item->name }}</div>
                                <div class="text-muted small">{{ $item->category->name ?? 'Uncategorized' }}</div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge {{ $item->current_stock > $item->reorder_point ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill px-2">
                                        {{ $item->current_stock }} Stock
                                    </span>
                                    <small class="text-muted">₱{{ number_format($item->price, 2) }}</small>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-muted opacity-25"></i>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">No inventory records found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    {{-- MOBILE ACTION SHEETS (Outside container) --}}
    @foreach($inventory as $item)
    <div class="modal fade" id="inventoryActionSheet-{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable fixed-bottom m-0" style="max-width: 100%;">
            <div class="modal-content rounded-top-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 justify-content-center">
                    <div class="bg-secondary bg-opacity-25 rounded-pill" style="width: 40px; height: 5px;"></div>
                </div>
                <div class="modal-body pt-4 pb-4">
                    <div class="text-center mb-4">
                        <h5 class="fw-bold mb-1">{{ $item->name }}</h5>
                        <p class="text-muted small mb-0">{{ $item->sku }}</p>
                    </div>
                    
                    <div class="d-grid gap-3">
                        <a href="{{ route('products.edit', $item->id) }}" class="btn btn-light shadow-sm p-3 rounded-4 d-flex align-items-center justify-content-center gap-2 fw-bold text-dark">
                            <i class="fas fa-edit fa-lg text-primary"></i> Edit Product
                        </a>
                        
                        <form action="{{ route('products.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Do you really want to delete this product?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-light shadow-sm p-3 rounded-4 w-100 d-flex align-items-center justify-content-center gap-2 fw-bold text-danger">
                                <i class="fas fa-trash fa-lg"></i> Delete Product
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

</div>
<style>
    .hover-primary:hover { background-color: #0d6efd !important; color: white !important; border-color: #0d6efd !important; }
</style>
@endsection