@extends('admin.layout')

@section('content')
<div class="container-fluid px-0 px-md-4">
    
    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between p-3 border-bottom bg-white shadow-sm sticky-top" style="z-index: 10;">
        <div class="d-flex align-items-center">
            <a href="{{ route('transactions.show', $sale->id) }}" class="btn btn-light rounded-circle me-3">
                <i class="fas fa-times"></i>
            </a>
            <div>
                <h6 class="mb-0 fw-bold">Process Return</h6>
                <small class="text-muted">Transaction #{{ $sale->id }}</small>
            </div>
        </div>
        <button type="submit" form="returnForm" class="btn btn-primary fw-bold px-4 rounded-pill">
            Confirm
        </button>
    </div>

    <form action="{{ route('admin.transactions.process_return', $sale->id) }}" method="POST" id="returnForm">
        @csrf
        <div class="row justify-content-center m-0 py-4">
            <div class="col-12 col-md-8 col-lg-6">
                
                {{-- INFO ALERT --}}
                <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4 d-flex">
                    <i class="fas fa-info-circle fs-4 me-3 mt-1"></i>
                    <div>
                        <strong>Return Policy:</strong>
                        <p class="mb-0 small opacity-75">Items marked "Good" return to stock. "Damaged" are disposed. Verification required.</p>
                    </div>
                </div>

                {{-- ITEMS LIST --}}
                @foreach($sale->saleItems as $index => $item)
                    @php
                        $returnedQty = \App\Models\SalesReturn::where('sale_id', $sale->id)
                                        ->where('product_id', $item->product_id)
                                        ->sum('quantity');
                        $remainingQty = $item->quantity - $returnedQty;
                    @endphp
                    
                    @if($remainingQty <= 0) @continue @endif

                    <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
                        <div class="card-body p-3">
                            {{-- Header: Name & Price --}}
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">{{ $item->product->name }}</h6>
                                    <div class="small text-muted">Sold @ ₱{{ number_format($item->price, 2) }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="badge bg-light text-dark border">
                                        Qty: {{ $remainingQty }}
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Inputs Grid --}}
                            <div class="bg-light p-3 rounded-3">
                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                
                                <div class="row g-2 align-items-end">
                                    {{-- QTY INPUT --}}
                                    <div class="col-4">
                                        <label class="small fw-bold text-uppercase text-muted mb-1">Return Qty</label>
                                        <input type="number" name="items[{{ $index }}][quantity]" 
                                               class="form-control text-center fw-bold shadow-sm border-0" 
                                               min="0" max="{{ $remainingQty }}" value="0"
                                               onfocus="this.select()">
                                    </div>

                                    {{-- CONDITION (Radio Toggles) --}}
                                    <div class="col-8">
                                        <label class="small fw-bold text-uppercase text-muted mb-1">Condition</label>
                                        <div class="d-flex gap-1">
                                            <input type="radio" class="btn-check" name="items[{{ $index }}][condition]" id="cond_good_{{ $index }}" value="good" checked>
                                            <label class="btn btn-outline-success btn-sm flex-fill fw-bold" for="cond_good_{{ $index }}">
                                                <i class="fas fa-check-circle me-1"></i>Good
                                            </label>

                                            <input type="radio" class="btn-check" name="items[{{ $index }}][condition]" id="cond_damaged_{{ $index }}" value="damaged">
                                            <label class="btn btn-outline-danger btn-sm flex-fill fw-bold" for="cond_damaged_{{ $index }}">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Damaged
                                            </label>
                                        </div>
                                    </div>

                                    {{-- REASON --}}
                                    <div class="col-12 mt-2">
                                        <input type="text" name="items[{{ $index }}][reason]" 
                                               class="form-control form-control-sm shadow-sm border-0" 
                                               placeholder="Reason (Optional)...">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach

                <div class="text-center py-4 text-muted small">
                    <i class="fas fa-lock me-1"></i> Actions are logged by {{ auth()->user()->name }}
                </div>
            </div>
        </div>
    </form>
</div>
@endsection