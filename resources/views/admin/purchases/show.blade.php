@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-truck-loading me-2"></i> Stock In Record #{{ $purchase->id }}</span>
                    <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-light text-success fw-bold">Back</a>
                </div>
                
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <h5 class="text-muted small mb-1">SUPPLIER</h5>
                            <h4 class="fw-bold text-dark">{{ $purchase->supplier->name ?? 'Unknown Supplier' }}</h4>
                            <p class="mb-0 text-muted small">{{ $purchase->supplier->contact_info ?? '' }}</p>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <h5 class="text-muted small mb-1">DATE RECEIVED</h5>
                            <h5 class="fw-bold">{{ $purchase->created_at->format('F d, Y') }}</h5>
                            <small class="text-muted">{{ $purchase->created_at->format('h:i A') }}</small>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th class="text-center">Qty Added</th>
                                    <th class="text-end">Unit Cost</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->purchaseItems as $item)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $item->product->name ?? 'Deleted Item' }}</span>
                                        <br>
                                        <small class="text-muted">{{ $item->product->category->name ?? '-' }}</small>
                                    </td>
                                    <td class="text-center fw-bold text-success">+{{ $item->quantity }}</td>
                                    <td class="text-end">₱{{ number_format($item->unit_cost, 2) }}</td>
                                    <td class="text-end">₱{{ number_format($item->quantity * $item->unit_cost, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">TOTAL COST</td>
                                    <td class="text-end fw-bold fs-5 text-success">₱{{ number_format($purchase->total_cost, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-3 p-3 bg-light rounded d-flex justify-content-between">
                        <small class="text-muted">Recorded by: <strong>{{ $purchase->user->name ?? 'System' }}</strong></small>
                        <small class="text-muted">Transaction ID: {{ $purchase->id }}</small>
                    </div>

                    {{-- NEW: VOID BUTTON --}}
                        <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" 
                              onsubmit="return confirm('CRITICAL WARNING: This will DEDUCT the stock quantities added in this purchase. \n\nAre you sure you want to void this transaction?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-trash-alt me-1"></i> Void Transaction
                            </button>
                        </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection