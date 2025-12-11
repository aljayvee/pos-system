@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span>Transaction Details #{{ $sale->id }}</span>
                    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-light">Back</a>
                </div>
                
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1 text-muted">Date:</p>
                            <h6 class="fw-bold">{{ $sale->created_at->format('F d, Y h:i A') }}</h6>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="mb-1 text-muted">Sold By:</p>
                            <h6 class="fw-bold">{{ $sale->user->name }}</h6>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr class="bg-light">
                                <th>Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->saleItems as $item)
                            <tr>
                                <td>{{ $item->product->name ?? 'Unknown Item' }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">₱{{ number_format($item->price, 2) }}</td>
                                <td class="text-end">₱{{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">TOTAL AMOUNT</td>
                                <td class="text-end fw-bold fs-5">₱{{ number_format($sale->total_amount, 2) }}</td>
                            </tr>
                            @if($sale->points_discount > 0)
                            <tr class="text-success">
                                <td colspan="3" class="text-end">Points Discount Used</td>
                                <td class="text-end">- ₱{{ number_format($sale->points_discount, 2) }}</td>
                            </tr>
                            @endif
                        </tfoot>
                    </table>

                    <div class="alert alert-warning mt-4">
                        <h6 class="fw-bold"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h6>
                        <p class="mb-2 small">Voiding this transaction will:</p>
                        <ul class="small mb-3">
                            <li>Restore stock quantity to inventory.</li>
                            <li>Remove sales record from reports.</li>
                            <li>Delete any linked Credit/Utang records.</li>
                            <li>Revert loyalty points earned or used.</li>
                        </ul>
                        
                        <form action="{{ route('transactions.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to VOID this transaction? This cannot be undone.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-ban me-1"></i> VOID TRANSACTION (REFUND)
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection