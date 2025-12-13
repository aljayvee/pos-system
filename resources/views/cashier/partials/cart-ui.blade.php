<div class="cart-container h-100 d-flex flex-column bg-light rounded-4 overflow-hidden border-0 shadow-sm">

    {{-- HEADER --}}
    <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center z-1">
        <h6 class="fw-bold m-0 text-dark d-flex align-items-center">
            <i class="fas fa-receipt me-2 text-primary"></i> Current Order
        </h6>
        <button class="btn btn-sm btn-link text-decoration-none text-danger fw-bold" onclick="clearCart()">
            Clear
        </button>
    </div>

    {{-- SCROLLABLE ITEMS --}}
    <div class="flex-grow-1 p-3 overflow-y-auto custom-scrollbar" id="cart-items" style="background: #f8fafc;">
        {{-- JS INJECTED HERE --}}
    </div>

    {{-- FOOTER / TOTALS --}}
    <div class="p-4 bg-white border-top rounded-top-4 shadow-lg z-2">
        
        {{-- Customer Select --}}
        <div class="mb-3">
            <label class="small fw-bold text-muted text-uppercase mb-1">Customer</label>
            <select id="customer-id" class="form-select border-0 bg-light fw-medium py-2">
                <option value="walk-in" data-points="0" data-balance="0">Walk-in Customer</option>
                <option value="new">+ Create New Profile</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" data-balance="{{ $c->balance ?? 0 }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Calculations --}}
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-secondary">Subtotal</span>
            <span class="fw-bold text-dark">₱<span id="subtotal-display">0.00</span></span>
        </div>

        <div id="tax-row" class="d-flex justify-content-between align-items-center mb-3" style="display:none;">
            <span class="text-secondary small">VAT (12%)</span>
            <span class="fw-bold text-dark small">₱<span id="tax-display">0.00</span></span>
        </div>

        <div class="d-flex justify-content-between align-items-end mb-4 pt-3 border-top">
            <span class="fw-bold text-dark h5 mb-0">Total Due</span>
            <span class="fw-bolder text-primary h3 mb-0">₱<span class="total-amount-display">0.00</span></span>
        </div>

        {{-- Checkout --}}
        <button class="btn btn-primary w-100 py-3 rounded-3 fw-bold shadow-md transition-all hover-scale" onclick="openPaymentModal()">
            PROCEED TO PAYMENT
        </button>
    </div>
</div>

<style>
    /* Utility for Cart */
    .hover-scale:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2); }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>