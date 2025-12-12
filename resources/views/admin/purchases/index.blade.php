@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-truck-loading text-primary"></i> Stock In / Purchase History</h2>
        <a href="{{ route('purchases.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> New Stock In
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Items Count</th>
                        <th>Total Cost</th>
                        <th>Recorded At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->id }}</td>
                        <td>{{ $purchase->formatted_date }}</td> <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                        
                        <td>{{ $purchase->items->count() }}</td> 
                        <td>₱{{ number_format($purchase->total_cost, 2) }}</td>
                        
                        <td>{{ $purchase->created_at->format('M d, Y h:i A') }}</td>
                        
                        <td>
                            <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-primary">
                                View Items
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection