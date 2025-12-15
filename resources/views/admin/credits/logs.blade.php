@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-4 mb-4 gap-2">
        <h1 class="h2 mb-0 text-gray-800"><i class="fas fa-clipboard-list text-secondary me-2"></i>Global Payment Logs</h1>
        <a href="{{ route('credits.index') }}" class="btn btn-outline-primary shadow-sm">
            <i class="fas fa-file-invoice-dollar me-1"></i> Outstanding Credits
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Timestamp</th>
                            <th class="py-3">Customer Info</th>
                            <th class="py-3">Amount Collected</th>
                            <th class="py-3">Processed By</th>
                            <th class="pe-4 py-3">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td class="ps-4 text-muted" style="min-width: 140px;">
                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }} <br>
                                <small>{{ \Carbon\Carbon::parse($payment->payment_date)->format('h:i A') }}</small>
                            </td>
                            <td>
                                <span class="fw-bold text-dark d-block">{{ $payment->credit->customer->name ?? 'Unknown Customer' }}</span>
                                <small class="text-muted">Credit ID #{{ $payment->customer_credit_id }}</small>
                            </td>
                            <td>
                                <span class="text-success fw-bold fs-6">+ ₱{{ number_format($payment->amount, 2) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">{{ $payment->user->name ?? 'System' }}</span>
                            </td>
                            <td class="pe-4 text-muted small" style="max-width: 250px;">
                                <div class="text-truncate">{{ $payment->notes ?? '-' }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-history fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">No payment history found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection