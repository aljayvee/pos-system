@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Payment History</h2>
            <small class="text-muted">For Credit ID #{{ $credit->credit_id }} - {{ $credit->customer->name }}</small>
        </div>
        <a href="{{ route('credits.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">Credit Details</div>
                <div class="card-body">
                    <p class="mb-1"><strong>Total Loan:</strong> ₱{{ number_format($credit->total_amount, 2) }}</p>
                    <p class="mb-1"><strong>Paid So Far:</strong> <span class="text-success">₱{{ number_format($credit->amount_paid, 2) }}</span></p>
                    <p class="mb-1"><strong>Remaining:</strong> <span class="text-danger fw-bold">₱{{ number_format($credit->remaining_balance, 2) }}</span></p>
                    <hr>
                    <p class="mb-0 small text-muted">Original Sale Date: {{ $credit->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">Transaction Log</div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Date Paid</th>
                                <th>Amount</th>
                                <th>Processed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y h:i A') }}</td>
                                <td class="text-success fw-bold">+ ₱{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->user->name ?? 'System' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">No payments recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection