@extends('admin.layout')

@section('content')
    <div class="container-fluid px-0 px-md-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark m-0">Transfer History</h3>
                <p class="text-muted small m-0">Log of all inventory movements.</p>
            </div>
            <a href="{{ route('transfers.create') }}" class="btn btn-primary shadow-lg rounded-pill px-4 fw-bold">
                <i class="fas fa-plus me-1"></i> New Transfer
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Date</th>
                            <th class="py-3">Product</th>
                            <th class="py-3">From</th>
                            <th class="py-3">To</th>
                            <th class="py-3">Qty</th>
                            <th class="py-3">User</th>
                            <th class="pe-4 py-3">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $transfer)
                            <tr>
                                <td class="ps-4 fw-bold text-secondary">{{ $transfer->created_at->format('M d, H:i') }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $transfer->product->name }}</div>
                                    <div class="small text-muted">{{ $transfer->product->sku }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $transfer->fromStore->name }}</span></td>
                                <td><span class="badge bg-light text-dark border">{{ $transfer->toStore->name }}</span></td>
                                <td><span class="fw-bold text-primary display-6 fs-6">{{ $transfer->quantity }}</span></td>
                                <td class="small">{{ $transfer->user->name }}</td>
                                <td class="pe-4 text-muted small fst-italic">{{ $transfer->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No transfer history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top-0 py-3">
                {{ $transfers->links() }}
            </div>
        </div>
    </div>
@endsection