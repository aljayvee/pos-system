<div class="cart-container h-100 d-flex flex-column bg-white rounded-4 overflow-hidden border shadow-sm">

    {{-- 1. HEADER --}}
    <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center flex-shrink-0">
        <h6 class="fw-bold m-0 text-dark d-flex align-items-center">
            <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px; height:32px;">
                <i class="fas fa-shopping-basket fa-sm"></i>
            </span>
            <span>Current Order</span>
        </h6>
        <button class="btn btn-sm text-muted fw-semibold" onclick="clearCart()" style="font-size: 0.85rem;">
            Clear All
        </button>
    </div>

    {{-- 2. SCROLLABLE BODY --}}
    <div class="flex-grow-1 p-3 overflow-y-auto custom-scrollbar" id="cart-items" style="background: #f8fafc;">
        {{-- Javascript injects items here --}}
    </div>

    {{-- 3. FOOTER --}}
    <div class="p-4 bg-white border-top flex-shrink-0" style="box-shadow: 0 -4px 12px rgba(0,0,0,0.02);">
        
        {{-- Customer Selector --}}
        <div class="mb-3">
            <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Customer</label>
            <div class="input-group">
                <span class="input-group-text bg-light border border-end-0"><i class="fas fa-user text-secondary"></i></span>
                <select id="customer-id" class="form-select border bg-light fw-semibold text-dark py-2 shadow-none">
                    <option value="walk-in" data-points="0" data-balance="0">Walk-in Customer</option>
                    <option value="new">+ Create New Profile</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" data-balance="{{ $c->balance ?? 0 }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Totals --}}
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-secondary fw-medium">Subtotal</span>
            <span class="fw-bold text-dark">₱<span class="subtotal-display">0.00</span></span>
        </div>

        <div class="tax-row d-flex justify-content-between align-items-center mb-3" style="display:none;">
            <span class="text-secondary small">VAT (12%)</span>
            <span class="fw-bold text-dark small">₱<span class="tax-display">0.00</span></span>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 pt-3 border-top">
            <span class="fw-bold text-dark h5 mb-0">Total</span>
            <span class="fw-bolder text-primary h3 mb-0">₱<span class="total-amount-display">0.00</span></span>
        </div>

        {{-- Checkout Button --}}
        <button class="btn btn-primary w-100 py-3 rounded-3 fw-bold shadow-sm d-flex justify-content-between align-items-center px-4" onclick="openPaymentModal()">
            <span>CHARGE</span>
            <span><i class="fas fa-arrow-right"></i></span>
        </button>
    </div>
</div>

<style>
    /* Custom Scrollbar for Cart */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>