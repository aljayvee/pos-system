@extends('admin.layout')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-file-invoice-dollar text-danger"></i> Outstanding Credits (Utang)</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
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
                        <td>
                            @if($credit->due_date)
                                {{ \Carbon\Carbon::parse($credit->due_date)->format('M d') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <button type="button" 
                                    class="btn btn-primary btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#repayModal"
                                    onclick="setRepayRoute({{ $credit->id }})">
                                Pay
                            </button>

                            <div class="modal fade" id="payModal{{ $credit->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Record Payment: {{ $credit->customer->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form id="repayForm" action="#" method="POST">
                                            @csrf
                                            @method('POST')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label>Remaining Balance</label>
                                                    <input type="text" class="form-control" value="₱{{ number_format($credit->remaining_balance, 2) }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Enter Payment Amount</label>
                                                    <input type="number" name="payment_amount" class="form-control" step="0.01" max="{{ $credit->remaining_balance }}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-success">Confirm Payment</button>
                                            </div>
                                        </form>
                                    </div>
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
<script>
    function setRepayRoute(id) {
        // 1. Get the form element
        var form = document.getElementById('repayForm');
        
        // 2. Generate the correct URL
        // We use a placeholder '000' and replace it with the actual ID
        var url = "{{ route('credits.repay', '000') }}";
        form.action = url.replace('000', id);
    }
</script>