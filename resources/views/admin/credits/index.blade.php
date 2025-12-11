@extends('admin.layout')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-file-invoice-dollar text-danger"></i> Outstanding Credits (Utang)</h2>

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
                            {{-- 1. Pass the unique ID to the modal target --}}
                            <button type="button" 
                                    class="btn btn-primary btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#repayModal-{{ $credit->id }}">
                                Pay
                            </button>

                            {{-- 2. Create a unique Modal ID for each row --}}
                            <div class="modal fade" id="repayModal-{{ $credit->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    {{-- 3. Set the action DIRECTLY here (No JS needed) --}}
                                    <form action="{{ route('credits.repay', $credit->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Repay Credit</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Amount</label>
                                                    {{-- Suggested amount: remaining balance --}}
                                                    <input type="number" class="form-control" name="payment_amount" 
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
{{-- Script removed because we handled the URL directly in Blade above --}}
@endsection