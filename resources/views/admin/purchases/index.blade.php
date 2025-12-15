@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-4 mb-4 gap-3">
        <h1 class="h2 mb-0 text-gray-800">
            <i class="fas fa-truck-loading text-primary me-2"></i>Stock In History
        </h1>
        <a href="{{ route('purchases.create') }}" class="btn btn-success shadow-sm btn-lg px-4">
            <i class="fas fa-plus me-2"></i> New Stock In
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- DESKTOP VIEW: Table --}}
    <div class="card shadow-sm border-0 d-none d-lg-block mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small fw-bold text-secondary">
                        <tr>
                            <th class="ps-4 py-3">ID</th>
                            <th class="py-3">Date</th>
                            <th class="py-3">Supplier</th>
                            <th class="text-center py-3">Items</th>
                            <th class="text-end py-3">Total Cost</th>
                            <th class="text-end pe-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td class="ps-4 text-muted">#{{ $purchase->id }}</td>
                            <td>
                                <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('M d, Y') }}</span>
                                <div class="small text-muted">{{ $purchase->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-light text-primary rounded-circle me-2 d-flex justify-content-center align-items-center" style="width:35px; height:35px">
                                        <i class="fas fa-building small"></i>
                                    </div>
                                    <span class="fw-bold">{{ $purchase->supplier->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">{{ $purchase->items->count() }} items</span>
                            </td>
                            <td class="text-end fw-bold text-success">₱{{ number_format($purchase->total_cost, 2) }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-outline-primary">
                                    View Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-clipboard-list fa-2x mb-2 opacity-25"></i>
                                <p class="mb-0">No purchase history found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($purchases->hasPages())
        <div class="card-footer bg-white border-top-0 d-flex justify-content-end py-3">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>

    {{-- MOBILE VIEW: Cards --}}
    <div class="d-lg-none">
        <div class="row g-3">
            @forelse($purchases as $purchase)
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Stock In #{{ $purchase->id }}</small>
                                <h5 class="fw-bold text-dark mb-0">{{ $purchase->supplier->name ?? 'Unknown Supplier' }}</h5>
                            </div>
                            <span class="badge bg-success-subtle text-success">Completed</span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center bg-light rounded p-2 mt-3">
                            <div>
                                <small class="d-block text-muted" style="font-size: 0.7rem;">DATE</small>
                                <span class="fw-bold">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('M d, Y') }}</span>
                            </div>
                            <div class="text-end">
                                <small class="d-block text-muted" style="font-size: 0.7rem;">TOTAL COST</small>
                                <span class="fw-bold text-success fs-5">₱{{ number_format($purchase->total_cost, 2) }}</span>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-outline-primary w-100">
                                View {{ $purchase->items->count() }} Items
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
                <p>No records found.</p>
            </div>
            @endforelse
        </div>
        <div class="mt-4">
             {{ $purchases->links() }}
        </div>
    </div>
</div>
@endsection