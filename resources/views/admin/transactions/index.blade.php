@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-receipt text-primary"></i> Transaction History</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('transactions.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Search by Sale ID or Reference No..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Reset</a>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Cashier</th>
                        <th>Customer</th>
                        <th>Method</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $sale)
                    <tr>
                        <td>#{{ $sale->id }}</td>
                        <td>{{ $sale->created_at->format('M d, Y h:i A') }}</td>
                        <td>{{ $sale->user->name }}</td>
                        <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                        <td>
                            <span class="badge {{ $sale->payment_method == 'credit' ? 'bg-warning text-dark' : 'bg-success' }}">
                                {{ ucfirst($sale->payment_method) }}
                            </span>
                        </td>
                        <td class="fw-bold">₱{{ number_format($sale->total_amount, 2) }}</td>
                        <td>
                            <a href="{{ route('transactions.show', $sale->id) }}" class="btn btn-sm btn-info text-white">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">No transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection