@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    
    {{-- MOBILE HEADER --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm px-3 py-3 d-flex align-items-center justify-content-between z-3 mb-3" style="top: 0;">
        <a href="{{ route('credits.index') }}" class="text-dark"><i class="fas fa-arrow-left fa-lg"></i></a>
        <h6 class="m-0 fw-bold text-dark">Payment Logs</h6>
         <div style="width: 24px;"></div> {{-- Spacer --}}
    </div>

    {{-- DESKTOP HEADER --}}
    <div class="d-none d-lg-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="fw-bold text-dark mb-1">
            <i class="fas fa-history text-success me-2"></i>Payment Logs
        </h4>
        <a href="{{ route('credits.index') }}" class="btn btn-light border shadow-sm rounded-pill fw-bold">
            <i class="fas fa-file-invoice-dollar me-1 text-secondary"></i> Outstanding Credits
        </a>
    </div>

    {{-- DESKTOP VIEW: TABLE --}}
    <div class="card shadow-sm border-0 d-none d-lg-block rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-list me-2 text-primary"></i>All Payments</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Date Paid</th>
                            <th class="py-3">Customer Information</th>
                            <th class="py-3">Amount Paid</th>
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
                                <span class="fw-bold text-dark">{{ $payment->credit->customer->name ?? 'Unknown' }}</span>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Credit ID: #{{ $payment->customer_credit_id }}</small>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fs-6">
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
                            <td class="pe-4 text-muted small fst-italic" style="max-width: 250px;">
                                {{ $payment->notes ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-history fa-2x mb-3 opacity-25"></i>
                                <p class="mb-0">No payment history found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MOBILE/PHABLET VIEW: CARDS --}}
    <div class="d-lg-none">
        <div class="vstack gap-3">
            @forelse($payments as $payment)
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-3">
                    {{-- Top Row: Customer & Amount --}}
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center">
                                {{ $payment->credit->customer->name ?? 'Unknown' }}
                            </h6>
                            <small class="text-muted" style="font-size: 0.75rem;">Credit #{{ $payment->customer_credit_id }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">+ ₱{{ number_format($payment->amount, 2) }}</span>
                            <div class="text-muted small mt-1" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, h:i A') }}</div>
                        </div>
                    </div>
                    
                    {{-- Middle Row: Info Box --}}
                    <div class="d-flex justify-content-between align-items-center bg-light rounded-3 p-2 mt-2">
                         <div class="d-flex align-items-center">
                             <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width:24px; height:24px;">
                                <i class="fas fa-user-tie text-secondary small"></i>
                             </div>
                             <small class="text-dark fw-bold">{{ $payment->user->name ?? 'System' }}</small>
                         </div>
                    </div>

                    {{-- Bottom Row: Notes --}}
                    @if($payment->notes)
                        <div class="small text-muted fst-italic mt-2 pt-2 border-top">
                            <i class="fas fa-sticky-note me-1 text-warning"></i> {{ $payment->notes }}
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fas fa-history fa-3x mb-3 opacity-25 text-light-gray"></i>
                <p>No payment history found.</p>
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection