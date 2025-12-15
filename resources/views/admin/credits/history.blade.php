@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    <div class="d-flex align-items-center justify-content-between mt-4 mb-4">
        <div>
            <h1 class="h2 mb-0 text-gray-800">Credit History</h1>
            <p class="text-muted small mb-0">Transaction Log for <strong>{{ $credit->customer->name }}</strong></p>
        </div>
        <a href="{{ route('credits.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-4">
        {{-- DETAILS CARD --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-info text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Summary</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted">Total Loan</span>
                            <span class="fw-bold">₱{{ number_format($credit->total_amount, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted">Paid Amount</span>
                            <span class="fw-bold text-success">₱{{ number_format($credit->amount_paid, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light">
                            <span class="text-uppercase fw-bold text-danger small">Remaining Balance</span>
                            <span class="fw-bold text-danger fs-5">₱{{ number_format($credit->remaining_balance, 2) }}</span>
                        </li>
                    </ul>
                    <div class="mt-4 text-center">
                         <small class="text-muted">Loan started on {{ $credit->created_at->format('M d, Y') }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- TRANSACTION LOGS --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-secondary">Payment Transactions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary small text-uppercase">
                                <tr>
                                    <th class="ps-4 py-3">Date</th>
                                    <th class="py-3">Amount</th>
                                    <th class="py-3">Processed By</th>
                                    <th class="pe-4 py-3">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                <tr>
                                    <td class="ps-4 text-muted">
                                        {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }} <br>
                                        <small>{{ \Carbon\Carbon::parse($payment->payment_date)->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success px-3 py-2">
                                            + ₱{{ number_format($payment->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs bg-light rounded-circle text-center me-2" style="width:25px; height:25px; line-height:25px">
                                                <i class="fas fa-user small text-muted"></i>
                                            </div>
                                            <span class="small">{{ $payment->user->name ?? 'System' }}</span>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-muted small fst-italic">
                                        {{ $payment->notes ?? '-' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-receipt fa-2x mb-3 opacity-25"></i>
                                        <p class="mb-0">No payments recorded yet.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection