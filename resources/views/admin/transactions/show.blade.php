@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mt-4 mb-4">
        <h1 class="h2 mb-0 text-gray-800">Transaction Details</h1>
        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <div class="card shadow border-0 mb-4">
                {{-- Card Header --}}
                <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-white-50 small text-uppercase fw-bold">Receipt No.</span>
                        <h5 class="mb-0 text-white">#{{ $sale->id }}</h5>
                    </div>
                    <div class="text-end">
                        <span class="badge {{ $sale->payment_method == 'credit' ? 'bg-danger' : 'bg-success' }} text-uppercase px-3 py-2">
                            {{ $sale->payment_method }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    {{-- Info Section --}}
                    <div class="bg-light p-4 border-bottom">
                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted text-uppercase fw-bold d-block">Date</small>
                                <span class="text-dark fw-bold">{{ $sale->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted text-uppercase fw-bold d-block">Cashier</small>
                                <span class="text-dark fw-bold">{{ $sale->user->name }}</span>
                            </div>
                            <div class="col-12 mt-3 pt-3 border-top">
                                <small class="text-muted text-uppercase fw-bold d-block">Customer</small>
                                <h5 class="fw-bold mb-0 text-primary">{{ $sale->customer->name ?? 'Walk-in Customer' }}</h5>
                            </div>
                        </div>
                    </div>

                    {{-- Items List --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-white text-uppercase small text-secondary border-bottom">
                                <tr>
                                    <th class="ps-4 py-3">Item</th>
                                    <th class="text-center py-3">Qty</th>
                                    <th class="text-end pe-4 py-3">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->saleItems as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $item->product->name ?? 'Unknown Item' }}</div>
                                        <small class="text-muted">@ ₱{{ number_format($item->price, 2) }}</small>
                                    </td>
                                    <td class="text-center fw-bold text-dark">x{{ $item->quantity }}</td>
                                    <td class="text-end pe-4 fw-bold">₱{{ number_format($item->price * $item->quantity, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                {{-- Discount Row --}}
                                @if($sale->points_discount > 0)
                                <tr>
                                    <td colspan="2" class="text-end text-success fw-bold py-2">Points Discount</td>
                                    <td class="text-end pe-4 text-success fw-bold py-2">- ₱{{ number_format($sale->points_discount, 2) }}</td>
                                </tr>
                                @endif
                                {{-- Total Row --}}
                                <tr>
                                    <td colspan="2" class="text-end text-dark fw-bold py-3 fs-5">TOTAL</td>
                                    <td class="text-end pe-4 text-primary fw-bold py-3 fs-4">₱{{ number_format($sale->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Admin Actions --}}
                <div class="card-footer bg-white p-3">
                    <h6 class="fw-bold text-secondary mb-3 small text-uppercase">Admin Actions</h6>
                    
                    <div class="row g-2">
                        {{-- Print --}}
                        <div class="col-12 col-sm-4">
                            <a href="{{ route('transactions.print', $sale->id) }}" target="_blank" class="btn btn-outline-dark w-100">
                                <i class="fas fa-print me-1"></i> Print
                            </a>
                        </div>

                        {{-- Return Items --}}
                        <div class="col-6 col-sm-4">
                            <a href="{{ route('admin.transactions.return', $sale->id) }}" class="btn btn-warning w-100">
                                <i class="fas fa-undo me-1"></i> Returns
                            </a>
                        </div>

                        {{-- Void Transaction --}}
                        <div class="col-6 col-sm-4">
                            <form action="{{ route('transactions.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('CRITICAL WARNING:\n\nThis will VOID the entire transaction:\n- Restore stock\n- Remove sales record\n- Cancel credit/points\n\nAre you sure?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-ban me-1"></i> Void
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection