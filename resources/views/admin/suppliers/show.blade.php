@extends('admin.layout')

@section('content')
<div class="container-fluid px-0 px-md-4 py-0 py-md-4">
    
    {{-- MOBILE HEADER --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm z-3">
        <div class="px-3 py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('suppliers.index') }}" class="text-dark"><i class="fas fa-arrow-left fa-lg"></i></a>
                <div>
                    <h6 class="m-0 fw-bold text-dark">{{ $supplier->name }}</h6>
                    <small class="text-muted" style="font-size: 0.7rem;">{{ $supplier->contact_info ?? 'No Contact Info' }}</small>
                </div>
            </div>
            <a href="{{ route('purchases.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-success rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                <i class="fas fa-cart-plus fa-sm"></i>
            </a>
        </div>
    </div>

    <div class="px-3 px-md-0 pt-3 pt-md-0">
        {{-- DESKTOP HEADER --}}
        <div class="d-none d-lg-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <a href="{{ route('suppliers.index') }}" class="btn btn-light border shadow-sm rounded-pill fw-bold mb-3 d-inline-block d-md-none">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3 d-none d-md-flex shadow-sm" 
                         style="width: 60px; height: 60px; font-size: 1.5rem;">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div>
                        <h2 class="h3 fw-bold text-dark mb-1">{{ $supplier->name }}</h2>
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-address-book me-1 text-secondary opacity-75"></i> {{ $supplier->contact_info ?? 'No Contact Information' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('suppliers.index') }}" class="btn btn-light border shadow-sm rounded-pill fw-bold d-none d-md-inline-block">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
                <a href="{{ route('purchases.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-success shadow-sm rounded-pill fw-bold px-4">
                    <i class="fas fa-cart-plus me-1"></i> New Purchase
                </a>
            </div>
        </div>

        {{-- STATS CARDS --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100 rounded-4" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); color: white;">
                    <div class="card-body p-4">
                        <p class="text-white-50 small text-uppercase fw-bold mb-1">Total Purchases Cost</p>
                        <h3 class="fw-bold mb-0">₱{{ number_format($totalSpent, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 bg-white">
                    <div class="card-body p-4">
                        <p class="text-muted small text-uppercase fw-bold mb-1">Total Restocks</p>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalTransactions) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100 rounded-4" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
                    <div class="card-body p-4">
                        <p class="text-white-50 small text-uppercase fw-bold mb-1">Last Restock Date</p>
                        <h3 class="fw-bold mb-0">{{ $lastPurchaseDate }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- RESTOCK HISTORY --}}
        <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-secondary"></i>Restocking History</h5>
            </div>
            
            {{-- Desktop Table --}}
            <div class="d-none d-lg-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary small text-uppercase fw-bold">
                            <tr>
                                <th class="ps-4 py-3">Date</th>
                                <th class="py-3">Reference / ID</th>
                                <th class="py-3">Received By</th>
                                <th class="text-end py-3">Total Cost</th>
                                <th class="text-center pe-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchases as $purchase)
                            <tr>
                                <td class="ps-4 text-muted fw-bold">{{ $purchase->created_at->format('M d, Y') }}</td>
                                <td><span class="badge bg-light text-dark border rounded-pill px-3">PO #{{ $purchase->id }}</span></td>
                                <td class="small">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width:24px; height:24px;">
                                            <i class="fas fa-user text-secondary" style="font-size: 0.7rem;"></i>
                                        </div>
                                        {{ $purchase->user->name ?? 'System' }}
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-danger">₱{{ number_format($purchase->total_cost, 2) }}</td>
                                <td class="text-center pe-4">
                                    <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-outline-primary shadow-sm rounded-pill fw-bold px-3">
                                        <i class="fas fa-eye me-1"></i> View Items
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No purchase records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile List View --}}
            <div class="d-lg-none bg-white p-3">
                <div class="list-group list-group-flush">
                    @forelse($purchases as $purchase)
                    <a href="{{ route('purchases.show', $purchase->id) }}" class="list-group-item list-group-item-action p-3 border rounded-4 mb-2 hover-bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-light text-dark border rounded-pill">PO #{{ $purchase->id }}</span>
                            <span class="text-muted small" style="font-size: 0.75rem;">{{ $purchase->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <div class="text-muted small mb-0">Total Cost</div>
                                <h5 class="fw-bold text-danger mb-0">₱{{ number_format($purchase->total_cost, 2) }}</h5>
                            </div>
                            <i class="fas fa-chevron-right text-muted opacity-25"></i>
                        </div>
                    </a>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-cart-arrow-down fa-3x mb-3 text-light-gray opacity-25"></i>
                        <p>No transactions found.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            @if($purchases->hasPages())
            <div class="card-footer bg-white py-3 border-top-0 d-flex justify-content-center">
                {{ $purchases->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection