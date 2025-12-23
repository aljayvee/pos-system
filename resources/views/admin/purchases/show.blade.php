@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Stock In Details</h4>
            <p class="text-muted small mb-0">Reference ID: #{{ $purchase->id }}</p>
        </div>
        <a href="{{ route('purchases.index') }}" class="btn btn-light border shadow-sm rounded-pill fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-success bg-opacity-10 text-success py-3 d-flex justify-content-between align-items-center border-bottom-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-file-invoice me-2"></i>Stock In Reference #{{ $purchase->id }}</h5>
                    <button class="btn btn-light text-success fw-bold shadow-sm rounded-pill px-3" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
                
                <div class="card-body p-4">
                    {{-- INFO HEADER --}}
                    <div class="row g-4 mb-4 border-bottom pb-4">
                        <div class="col-12 col-md-6">
                            <label class="text-uppercase text-secondary small fw-bold">Supplier</label>
                            <div class="d-flex align-items-start mt-2">
                                <div class="bg-light rounded-circle p-3 me-3 text-primary shadow-sm">
                                    <i class="fas fa-building fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
                                        {{ $purchase->supplier->name ?? 'Unknown Supplier' }}
                                    </h5>
                                    <p class="text-muted mb-0 small">{{ $purchase->supplier->contact_info ?? 'No contact info' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 text-md-end">
                            <div class="bg-light rounded-4 p-3 d-inline-block text-start text-md-end" style="min-width: 200px;">
                                <label class="text-uppercase text-secondary small fw-bold d-block">Date Received</label>
                                <h5 class="fw-bold text-dark mb-0">{{ $purchase->created_at->format('M d, Y') }}</h5>
                                <small class="text-muted">{{ $purchase->created_at->format('h:i A') }}</small>
                                <div class="mt-2 text-md-end">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">
                                        <i class="fas fa-user-check me-1"></i> {{ $purchase->user->name ?? 'System' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ITEMS TABLE --}}
                    <h6 class="fw-bold text-secondary mb-3 text-uppercase small"><i class="fas fa-list me-2"></i>Items Received</h6>
                    <div class="table-responsive rounded-4 border border-light overflow-hidden mb-4">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 ps-4" style="min-width: 200px;">Product Info</th>
                                    <th class="text-center py-3">Qty Added</th>
                                    <th class="text-end py-3">Unit Cost</th>
                                    <th class="text-end fw-bold pe-4 py-3">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->purchaseItems as $item)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-dark">{{ $item->product->name ?? 'Deleted Item' }}</span>
                                        <div class="small text-muted">{{ $item->product->category->name ?? '-' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6 rounded-pill">+{{ $item->quantity }}</span>
                                    </td>
                                    <td class="text-end text-muted">₱{{ number_format($item->unit_cost, 2) }}</td>
                                    <td class="text-end fw-bold pe-4">₱{{ number_format($item->quantity * $item->unit_cost, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold text-uppercase py-4 text-secondary">Total Stock In Cost</td>
                                    <td class="text-end fw-bold fs-3 text-success py-4 pe-4">₱{{ number_format($purchase->total_cost, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="mt-4 pt-3 d-flex justify-content-between align-items-center">
                        <small class="text-muted fst-italic"><i class="fas fa-info-circle me-1"></i>Transaction ID: {{ $purchase->id }}</small>
                        
                        <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" 
                              onsubmit="return confirm('CRITICAL WARNING:\n\nThis will REMOVE the stock quantities added in this purchase from your inventory.\n\nAre you sure you want to void this?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger rounded-pill fw-bold px-4 shadow-sm border-0 bg-danger-subtle text-danger">
                                <i class="fas fa-ban me-1"></i> Void Transaction
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * { visibility: hidden; }
        .card, .card * { visibility: visible; }
        .card { position: absolute; left: 0; top: 0; width: 100%; border: none !important; box-shadow: none !important; }
        .btn, .no-print { display: none !important; }
    }
</style>
@endsection