@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-4 mb-4 gap-2">
        <h1 class="h2 mb-0 text-gray-800"><i class="fas fa-history text-success me-2"></i>Payment Logs</h1>
        <a href="{{ route('credits.index') }}" class="btn btn-outline-primary shadow-sm">
            <i class="fas fa-file-invoice-dollar me-1"></i> Outstanding Credits
        </a>
    </div>

    {{-- DESKTOP VIEW: TABLE --}}
    <div class="card shadow-sm border-0 d-none d-lg-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Date Paid</th>
                            <th class="py-3">Customer</th>
                            <th class="py-3">Amount Paid</th>
                            <th class="py-3">Processed By</th>
                            <th class="pe-4 py-3">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td class="ps-4 text-muted">
                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y h:i A') }}
                            </td>
                            <td>
                                <span class="fw-bold text-dark">{{ $payment->credit->customer->name ?? 'Unknown' }}</span>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Credit ID: #{{ $payment->customer_credit_id }}</small>
                            </td>
                            <td>
                                <span class="text-success fw-bold">+ ₱{{ number_format($payment->amount, 2) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">{{ $payment->user->name ?? 'System' }}</span>
                            </td>
                            <td class="pe-4 text-muted small fst-italic">
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
        <div class="row g-3">
            @forelse($payments as $payment)
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-0 border-start border-4 border-success h-100">
                    <div class="card-body">
                        {{-- Top Row: Customer & Amount --}}
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">{{ $payment->credit->customer->name ?? 'Unknown' }}</h6>
                                <small class="text-muted" style="font-size: 0.75rem;">Credit #{{ $payment->customer_credit_id }}</small>
                            </div>
                            <div class="text-end">
                                <span class="d-block text-success fw-bold fs-5">+ ₱{{ number_format($payment->amount, 2) }}</span>
                            </div>
                        </div>
                        
                        {{-- Middle Row: Info Box --}}
                        <div class="d-flex justify-content-between align-items-center bg-light rounded p-2 mb-2">
                             <small class="text-muted"><i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</small>
                             <div class="d-flex align-items-center">
                                 <i class="fas fa-user-circle text-secondary me-1"></i>
                                 <small class="text-dark fw-bold">{{ $payment->user->name ?? 'System' }}</small>
                             </div>
                        </div>

                        {{-- Bottom Row: Notes --}}
                        @if($payment->notes)
                            <div class="small text-muted fst-italic mt-2 border-top pt-2">
                                <i class="fas fa-sticky-note me-1 text-warning"></i> {{ $payment->notes }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fas fa-history fa-3x mb-3 opacity-25"></i>
                <p>No payment history found.</p>
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection