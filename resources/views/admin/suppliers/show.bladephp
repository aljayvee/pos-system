@extends('admin.layout')

@section('content')
<div class="container py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0 text-primary"><i class="fas fa-truck me-2"></i> {{ $supplier->name }}</h2>
            <p class="text-muted mb-0">
                <i class="fas fa-phone-alt me-1"></i> {{ $supplier->contact_person ?? 'No Contact' }} | 
                <i class="fas fa-mobile-alt me-1"></i> {{ $supplier->phone ?? 'No Phone' }}
            </p>
        </div>
        <div>
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            <a href="{{ route('purchases.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Purchase
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-danger text-white h-100 shadow-sm">
                <div class="card-body">
                    <small class="opacity-75">Total Purchased (Cost)</small>
                    <h3 class="fw-bold mb-0">₱{{ number_format($totalSpent, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <small class="text-muted">Total Restocks</small>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalTransactions) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <small class="text-white-50">Last Restock Date</small>
                    <h3 class="fw-bold mb-0">{{ $lastPurchaseDate }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Purchase History Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-history text-muted me-2"></i> Restocking History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Reference / ID</th>
                            <th>Received By</th>
                            <th class="text-end">Total Cost</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->purchase_date->format('M d, Y') }}</td>
                            <td><span class="badge bg-light text-dark border">PO #{{ $purchase->id }}</span></td>
                            <td class="small">{{ $purchase->user->name ?? 'System' }}</td>
                            <td class="text-end fw-bold">₱{{ number_format($purchase->total_cost, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View Items
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i><br>
                                No purchase records found for this supplier.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $purchases->links() }}
        </div>
    </div>
</div>
@endsection