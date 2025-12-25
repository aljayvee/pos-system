@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    {{-- MOBILE HEADER --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm px-3 py-3 d-flex align-items-center justify-content-between z-3 mb-3" style="top: 0;">
        <div style="width: 40px;"></div>
        <h6 class="m-0 fw-bold text-dark">Stock History</h6>
        <div style="width: 40px;"></div>
    </div>

    {{-- HEADER --}}
    <div class="d-none d-lg-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="fw-bold text-dark mb-1">
            <i class="fas fa-truck-loading text-success me-2"></i>Stock In History
        </h4>
        <a href="{{ route('purchases.create') }}" class="btn btn-success shadow-sm rounded-pill fw-bold px-4">
            <i class="fas fa-plus me-1"></i> New Stock In
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 border-0 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- DESKTOP VIEW: Table --}}
    <div class="card shadow-sm border-0 d-none d-lg-block mb-4 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-list me-2 text-primary"></i>Recent Restocks</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small fw-bold text-secondary">
                        <tr>
                            <th class="ps-4 py-3">Reference</th>
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
                            <td class="ps-4 text-muted"><span class="badge bg-light text-dark border rounded-pill px-3">#{{ $purchase->id }}</span></td>
                            <td>
                                <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('M d, Y') }}</span>
                                <div class="small text-muted">{{ $purchase->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle me-2 d-flex justify-content-center align-items-center" style="width:30px; height:30px">
                                        <i class="fas fa-building small text-secondary"></i>
                                    </div>
                                    <span class="fw-bold text-dark">{{ $purchase->supplier->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 border border-secondary-subtle">{{ $purchase->items->count() }} items</span>
                            </td>
                            <td class="text-end fw-bold text-success">₱{{ number_format($purchase->total_cost, 2) }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-light border text-primary fw-bold rounded-pill px-3 shadow-sm">
                                    View Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No purchase history found.</td>
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

    {{-- === MOBILE NATIVE VIEW (List) === --}}
    <div class="d-lg-none card shadow-sm border-0 rounded-4 overflow-hidden mb-5">
        <ul class="list-group list-group-flush">
            @forelse($purchases as $purchase)
            <li class="list-group-item p-3 border-bottom-0 hover-bg-light">
                <a href="{{ route('purchases.show', $purchase->id) }}" class="text-decoration-none text-dark d-flex align-items-center gap-3">
                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                        <i class="fas fa-truck text-secondary fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="fw-bold text-dark mb-0">{{ $purchase->supplier->name ?? 'Unknown' }}</h6>
                            <span class="fw-bold text-success">₱{{ number_format($purchase->total_cost, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('M d, Y') }}</small>
                            </div>
                            <span class="badge bg-light text-secondary border rounded-pill small">{{ $purchase->items->count() }} Items</span>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>
            </li>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-box-open fa-3x mb-3 opacity-25 text-light-gray"></i>
                <p>No records found.</p>
            </div>
            @endforelse
        </ul>
        @if($purchases->hasPages())
        <div class="p-3 border-top">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>

    {{-- MOBILE FAB --}}
    <div class="d-lg-none position-fixed p-3 z-3 end-0" style="bottom: 80px;">
        <a href="{{ route('purchases.create') }}" class="btn btn-success shadow-lg rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
            <i class="fas fa-plus fa-lg"></i>
        </a>
    </div>
</div>
@endsection