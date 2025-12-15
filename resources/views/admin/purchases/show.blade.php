@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    <div class="mt-4 mb-4">
        <a href="{{ route('purchases.index') }}" class="text-decoration-none text-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to History
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Stock In #{{ $purchase->id }}</h5>
                    <button class="btn btn-sm btn-light text-success fw-bold d-none d-md-inline-block" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
                
                <div class="card-body p-4">
                    {{-- INFO HEADER --}}
                    <div class="row g-4 mb-4 border-bottom pb-4">
                        <div class="col-12 col-md-6">
                            <label class="text-uppercase text-muted small fw-bold">Supplier</label>
                            <h4 class="fw-bold text-dark mb-0">{{ $purchase->supplier->name ?? 'Unknown Supplier' }}</h4>
                            <p class="text-muted mb-0">{{ $purchase->supplier->contact_info ?? 'No contact info' }}</p>
                        </div>
                        <div class="col-12 col-md-6 text-md-end">
                            <label class="text-uppercase text-muted small fw-bold">Date Received</label>
                            <h4 class="fw-bold text-dark mb-0">{{ $purchase->created_at->format('M d, Y') }}</h4>
                            <small class="text-muted">{{ $purchase->created_at->format('h:i A') }}</small>
                            <div class="mt-2">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                    Processed by: {{ $purchase->user->name ?? 'System' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- ITEMS TABLE --}}
                    <h6 class="fw-bold text-secondary mb-3">Items Received</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th style="min-width: 200px;">Product Name</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Cost</th>
                                    <th class="text-end fw-bold">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->purchaseItems as $item)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $item->product->name ?? 'Deleted Item' }}</span>
                                        <div class="small text-muted">{{ $item->product->category->name ?? '-' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success px-3 py-2 fs-6">+{{ $item->quantity }}</span>
                                    </td>
                                    <td class="text-end text-muted">₱{{ number_format($item->unit_cost, 2) }}</td>
                                    <td class="text-end fw-bold">₱{{ number_format($item->quantity * $item->unit_cost, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold text-uppercase py-3">Total Cost</td>
                                    <td class="text-end fw-bold fs-4 text-success py-3">₱{{ number_format($purchase->total_cost, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted fst-italic">Transaction ID: {{ $purchase->id }}</small>
                        
                        <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" 
                              onsubmit="return confirm('CRITICAL WARNING:\n\nThis will REMOVE the stock quantities added in this purchase from your inventory.\n\nAre you sure you want to void this?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
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