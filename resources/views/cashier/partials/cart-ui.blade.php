<div class="cart-container border-0 h-100">

{{-- NEW: Header with Clear Button --}}
    <div class="p-3 border-bottom bg-white d-flex justify-content-between align-items-center rounded-top-3">
        <h6 class="fw-bold m-0"><i class="fas fa-shopping-cart me-2 text-primary"></i>Current Order</h6>
        <button class="btn btn-sm btn-outline-danger border-0" onclick="clearCart()" title="Clear Cart">
            <i class="fas fa-trash-alt"></i> Clear
        </button>
    </div>

    <div class="cart-items-area p-3" id="cart-items">
        {{-- Javascript will inject items here --}}
        <div class="text-center text-muted mt-5">
            <i class="fas fa-basket-shopping fa-3x opacity-25"></i>
            <p>Cart is empty</p>
        </div>
    </div>

    
    
    <div class="p-3 bg-light border-top">
        {{-- Customer Select --}}
        <select id="customer-id" class="form-select mb-2 shadow-sm">
            <option value="walk-in" data-points="0" data-balance="0">Walk-in Customer</option>
            <option value="new" data-points="0">+ New (Credit)</option>
            @foreach($customers as $c)
            <option value="{{ $c->id }}" data-balance="{{ $c->balance ?? 0 }}" data-points="{{ $c->points }}">{{ $c->name }}</option>
            @endforeach
        </select>

        {{-- Totals --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold m-0">Total</h3>
            <h2 class="fw-bold text-primary m-0">₱<span class="total-amount-display">0.00</span></h2>
        </div>

        {{-- Checkout Button --}}
        <button class="btn btn-primary w-100 py-3 rounded-3 fw-bold fs-5 shadow-sm" onclick="openPaymentModal()">
            PAY NOW
        </button>
    </div>
</div>