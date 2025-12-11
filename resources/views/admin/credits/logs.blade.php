@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-history text-success"></i> Payment Logs (All)</h2>
        <a href="{{ route('credits.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-file-invoice-dollar"></i> View Outstanding Credits
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Date Paid</th>
                            <th>Customer</th>
                            <th>Amount Paid</th>
                            <th>Processed By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y h:i A') }}</td>
                            <td class="fw-bold">
                                {{ $payment->credit->customer->name ?? 'Unknown' }}
                                <small class="text-muted d-block">Credit ID: #{{ $payment->customer_credit_id }}</small>
                            </td>
                            <td class="text-success fw-bold">+ ₱{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->user->name ?? 'System' }}</td>
                            <td>{{ $payment->notes ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No payment history found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection