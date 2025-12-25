@extends('admin.layout')

@section('content')
<div class="container-fluid px-0 px-md-4 py-0 py-md-4">
    
    {{-- FORM START --}}
    <form action="{{ route('admin.transactions.process_return', $sale->id) }}" method="POST" id="returnForm">
        @csrf
        
        {{-- MOBILE HEADER --}}
        <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm z-3">
            <div class="px-3 py-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('transactions.show', $sale->id) }}" class="text-dark"><i class="fas fa-arrow-left fa-lg"></i></a>
                    <h6 class="m-0 fw-bold text-dark">Process Return</h6>
                </div>
                <button type="submit" class="btn btn-primary btn-sm rounded-pill fw-bold px-3 shadow-sm">
                    Confirm
                </button>
            </div>
        </div>

        {{-- DESKTOP HEADER --}}
        <div class="d-none d-lg-flex align-items-center justify-content-between p-3 border-bottom bg-white shadow-sm sticky-top mb-4 rounded-3" style="z-index: 10; top: 20px;">
            <div class="d-flex align-items-center">
                <a href="{{ route('transactions.show', $sale->id) }}" class="btn btn-light rounded-circle me-3">
                    <i class="fas fa-times"></i>
                </a>
                <div>
                    <h6 class="mb-0 fw-bold">Process Return</h6>
                    <small class="text-muted">Transaction #{{ $sale->id }}</small>
                </div>
            </div>
            <button type="submit" class="btn btn-primary fw-bold px-4 rounded-pill">
                Confirm Return
            </button>
        </div>

        <div class="row justify-content-center m-0 py-4 pt-md-0">
            <div class="col-12 col-md-8 col-lg-6 px-3 px-md-0">
                
                {{-- INFO ALERT --}}
                <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
                    <i class="fas fa-info-circle fs-4 me-3 text-info"></i>
                    <div>
                        <strong class="d-block text-dark">Return Policy</strong>
                        <p class="mb-0 small text-muted opacity-75">Items marked "Good" return to stock. "Damaged" will be disposed.</p>
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
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">{{ $item->product->name }}</h6>
                                    <div class="small text-muted">Sold @ ₱{{ number_format($item->price, 2) }}</div>
                                </div>
                                <span class="badge bg-light text-dark border">Qty: {{ $remainingQty }}</span>
                            </div>
                        </div>

                        <div class="card-body p-3 pt-0">
                            {{-- Inputs Grid --}}
                            <div class="bg-light p-3 rounded-4">
                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                
                                <div class="row g-3">
                                    {{-- QTY INPUT --}}
                                    <div class="col-4">
                                        <label class="small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.65rem;">Return Qty</label>
                                        <input type="number" name="items[{{ $index }}][quantity]" 
                                               class="form-control text-center fw-bold shadow-sm border-0 py-2" 
                                               min="0" max="{{ $remainingQty }}" value="0"
                                               style="font-size: 1.1rem;"
                                               onfocus="this.select()">
                                    </div>

                                    {{-- CONDITION (Radio Toggles) --}}
                                    <div class="col-8">
                                        <label class="small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.65rem;">Condition</label>
                                        <div class="d-flex gap-2">
                                            <input type="radio" class="btn-check" name="items[{{ $index }}][condition]" id="cond_good_{{ $index }}" value="good" checked>
                                            <label class="btn btn-outline-success btn-sm flex-fill fw-bold py-2 rounded-3 small" for="cond_good_{{ $index }}">
                                                <i class="fas fa-check me-1"></i>Good
                                            </label>

                                            <input type="radio" class="btn-check" name="items[{{ $index }}][condition]" id="cond_damaged_{{ $index }}" value="damaged">
                                            <label class="btn btn-outline-danger btn-sm flex-fill fw-bold py-2 rounded-3 small" for="cond_damaged_{{ $index }}">
                                                <i class="fas fa-times me-1"></i>Damaged
                                            </label>
                                        </div>
                                    </div>

                                    {{-- REASON --}}
                                    <div class="col-12">
                                        <input type="text" name="items[{{ $index }}][reason]" 
                                               class="form-control form-control-sm shadow-sm border-0 py-2 px-3 rounded-3" 
                                               placeholder="Reason for return (Optional)...">
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