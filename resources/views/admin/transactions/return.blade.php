@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-undo me-2"></i>Process Return for Sale #{{ $sale->id }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.transactions.process_return', $sale->id) }}" method="POST">
                        @csrf
                        
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Sold Qty</th>
                                        <th>Price</th>
                                        <th>Return Qty</th>
                                        <th>Condition</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->saleItems as $index => $item)
                                    <tr>
                                        <td>
                                            {{ $item->product->name }}
                                            <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td>₱{{ number_format($item->price, 2) }}</td>
                                        
                                        {{-- Return Quantity Input --}}
                                        <td>
                                            <input type="number" name="items[{{ $index }}][quantity]" 
                                                   class="form-control form-control-sm" 
                                                   min="0" max="{{ $item->quantity }}" value="0">
                                        </td>

                                        {{-- Condition Select --}}
                                        <td>
                                            <select name="items[{{ $index }}][condition]" class="form-select form-select-sm">
                                                <option value="good">Good (Restock)</option>
                                                <option value="damaged">Damaged (Dispose)</option>
                                            </select>
                                        </td>

                                        {{-- Reason Input --}}
                                        <td>
                                            <input type="text" name="items[{{ $index }}][reason]" 
                                                   class="form-control form-control-sm" 
                                                   placeholder="e.g. Expired, Wrong Item">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle"></i> 
                            Items returned in <strong>Good</strong> condition will be added back to inventory.<br>
                            If this was a <strong>Credit (Utang)</strong> sale, the customer's debt balance will be reduced automatically.
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('transactions.show', $sale->id) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-danger">Confirm Return</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection