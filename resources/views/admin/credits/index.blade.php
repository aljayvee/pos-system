@extends('admin.layout')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-file-invoice-dollar text-danger"></i> Outstanding Credits (Utang)</h2>

    {{-- NEW EXPORT BUTTON --}}
        <a href="{{ route('credits.export') }}" class="btn btn-success">
            <i class="fas fa-file-download"></i> Export List
        </a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total Loan</th>
                        <th>Paid So Far</th>
                        <th>Balance</th>
                        <th>Due Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($credits as $credit)
                    <tr>
                        <td class="fw-bold">{{ $credit->customer->name }}</td>
                        <td>{{ $credit->created_at->format('M d, Y') }}</td>
                        <td>₱{{ number_format($credit->total_amount, 2) }}</td>
                        <td class="text-success">₱{{ number_format($credit->amount_paid, 2) }}</td>
                        <td class="text-danger fw-bold">₱{{ number_format($credit->remaining_balance, 2) }}</td>
                        <td>{{ $credit->due_date ? \Carbon\Carbon::parse($credit->due_date)->format('M d') : '-' }}</td>
                        <td>
                            {{-- USE credit_id HERE --}}
                            <button type="button" 
                                    class="btn btn-primary btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#repayModal-{{ $credit->credit_id }}">
                                Pay
                            </button>

                            {{-- NEW: History Button --}}
                            <a href="{{ route('credits.history', $credit->credit_id) }}" class="btn btn-secondary btn-sm ms-1">
                                <i class="fas fa-history"></i> History
                            </a>

                            {{-- USE credit_id HERE FOR ID --}}
                            <div class="modal fade" id="repayModal-{{ $credit->credit_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    {{-- USE credit_id HERE FOR ROUTE --}}
                                    <form action="{{ route('credits.repay', $credit->credit_id) }}" method="POST">
                                        @csrf
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Repay Credit - {{ $credit->customer->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Payment Amount</label>
                                                    <input type="number" class="form-control" name="payment_amount" 
                                                           step="0.01" 
                                                           max="{{ $credit->remaining_balance }}" 
                                                           value="{{ $credit->remaining_balance }}" required>
                                                </div>
                                            </div>
                                            
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Confirm Payment</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No outstanding debts found. Good job!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection