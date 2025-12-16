@extends('admin.layout')

@section('content')
<div class="container-fluid px-1 py-14">
    <h1 class="mt-4">Stock Adjustment History</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active">History</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-history me-1"></i>
            Log of Damages, Wastage, and Corrections
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Date/Time</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Adjusted By</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                    <tr>
                        <td>{{ $adj->created_at->format('M d, Y h:i A') }}</td>
                        <td>{{ $adj->product->name ?? 'Unknown' }}</td>
                        <td>
                            @if($adj->type == 'wastage') <span class="badge bg-danger">Wastage</span>
                            @elseif($adj->type == 'damage') <span class="badge bg-warning text-dark">Damage</span>
                            @elseif($adj->type == 'loss') <span class="badge bg-dark">Loss/Theft</span>
                            @else <span class="badge bg-secondary">{{ ucfirst($adj->type) }}</span>
                            @endif
                        </td>
                        <td class="text-danger fw-bold">-{{ $adj->quantity }}</td>
                        <td>{{ $adj->user->name ?? 'System' }}</td>
                        <td>{{ $adj->remarks }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No adjustment records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection