@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Credit History</h4>
            <p class="text-muted small mb-0">Transaction Log for <strong>{{ $credit->customer->name }}</strong></p>
        </div>
        <a href="{{ route('credits.index') }}" class="btn btn-light border shadow-sm rounded-pill fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-4">
        {{-- DETAILS CARD --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden">
                <div class="card-header bg-info bg-opacity-10 text-dark fw-bold py-3 border-bottom-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-info"></i>Account Summary</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 border-light">
                            <span class="text-muted small text-uppercase fw-bold">Total Loan</span>
                            <span class="fw-bold text-dark">₱{{ number_format($credit->total_amount, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 border-light">
                            <span class="text-muted small text-uppercase fw-bold">Amount Paid</span>
                            <span class="fw-bold text-success">₱{{ number_format($credit->amount_paid, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 bg-light">
                            <span class="text-uppercase fw-bold text-danger small">Balance Due</span>
                            <span class="fw-bold text-danger fs-5">₱{{ number_format($credit->remaining_balance, 2) }}</span>
                        </li>
                    </ul>
                    <div class="p-4 text-center">
                         <div class="text-muted small"><i class="far fa-calendar-alt me-1"></i> Loan started on {{ $credit->created_at->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TRANSACTION LOGS --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-2 text-primary"></i>Payment Transactions</h5>
                </div>
                
                {{-- Desktop Table --}}
                <div class="card-body p-0 d-none d-lg-block">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary small text-uppercase fw-bold">
                                <tr>
                                    <th class="ps-4 py-3">Date Processed</th>
                                    <th class="py-3">Amount</th>
                                    <th class="py-3">Processed By</th>
                                    <th class="pe-4 py-3">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                <tr>
                                    <td class="ps-4 text-muted">
                                        <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</div>
                                        <small>{{ \Carbon\Carbon::parse($payment->payment_date)->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                            + ₱{{ number_format($payment->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width:28px; height:28px;">
                                                <i class="fas fa-user small text-secondary"></i>
                                            </div>
                                            <span class="small fw-bold">{{ $payment->user->name ?? 'System' }}</span>
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

                {{-- Mobile List View --}}
                <div class="card-body p-3 d-lg-none bg-light">
                     @forelse($payments as $payment)
                    <div class="card border-0 shadow-sm mb-3 rounded-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small"><i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, h:i A') }}</span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">+ ₱{{ number_format($payment->amount, 2) }}</span>
                            </div>
                            
                            <div class="d-flex align-items-center mt-3 bg-light rounded-3 p-2">
                                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width:24px; height:24px;">
                                    <i class="fas fa-user-circle text-secondary" style="font-size: 0.8rem;"></i>
                                </div>
                                <small class="text-muted fw-bold">{{ $payment->user->name ?? 'System' }}</small>
                            </div>
                            
                            @if($payment->notes)
                            <div class="mt-2 small text-muted fst-italic border-top pt-2">
                                "{{ $payment->notes }}"
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-receipt fa-3x mb-3 text-light-gray opacity-25"></i>
                        <p>No payments recorded yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection