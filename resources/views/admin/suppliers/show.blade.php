@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-4 mb-4 gap-3">
        <div>
            <h1 class="h2 mb-0 text-gray-800">{{ $supplier->name }}</h1>
            <p class="text-muted mb-0 small">
                <i class="fas fa-address-book me-1"></i> {{ $supplier->contact_info ?? 'No Contact Information' }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary shadow-sm flex-fill flex-md-grow-0">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('purchases.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-success shadow-sm flex-fill flex-md-grow-0">
                <i class="fas fa-cart-plus me-1"></i> New Purchase
            </a>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card bg-danger text-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <p class="text-white-50 small text-uppercase fw-bold mb-1">Total Purchases Cost</p>
                    <h3 class="fw-bold mb-0">₱{{ number_format($totalSpent, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card bg-white h-100 shadow-sm border-0 border-start border-4 border-dark">
                <div class="card-body">
                    <p class="text-muted small text-uppercase fw-bold mb-1">Total Restocks</p>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalTransactions) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card bg-info text-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <p class="text-white-50 small text-uppercase fw-bold mb-1">Last Restock Date</p>
                    <h3 class="fw-bold mb-0">{{ $lastPurchaseDate }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- RESTOCK HISTORY --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-secondary"><i class="fas fa-history me-2"></i>Restocking History</h5>
        </div>
        
        {{-- Desktop Table --}}
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase">
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
                            <td class="ps-4 text-muted">{{ $purchase->created_at->format('M d, Y') }}</td>
                            <td><span class="badge bg-light text-dark border">PO #{{ $purchase->id }}</span></td>
                            <td class="small">{{ $purchase->user->name ?? 'System' }}</td>
                            <td class="text-end fw-bold text-danger">₱{{ number_format($purchase->total_cost, 2) }}</td>
                            <td class="text-center pe-4">
                                <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-outline-primary shadow-sm">
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
        <div class="d-lg-none">
            <ul class="list-group list-group-flush">
                @forelse($purchases as $purchase)
                <li class="list-group-item p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-light text-dark border">PO #{{ $purchase->id }}</span>
                            <span class="text-muted small ms-2">{{ $purchase->created_at->format('M d, Y') }}</span>
                        </div>
                        <span class="fw-bold text-danger fs-5">₱{{ number_format($purchase->total_cost, 2) }}</span>
                    </div>
                    <div class="d-grid">
                        <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-outline-primary">
                            View Details
                        </a>
                    </div>
                </li>
                @empty
                <li class="list-group-item text-center py-4 text-muted">No history found.</li>
                @endforelse
            </ul>
        </div>

        @if($purchases->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>
</div>
@endsection