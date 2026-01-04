<div class="cart-container h-100 d-flex flex-column bg-white rounded-4 overflow-hidden border shadow-sm">

    {{-- 1. HEADER --}}
    <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center flex-shrink-0">
        <h6 class="fw-bold m-0 text-dark d-flex align-items-center">
            <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                style="width:32px; height:32px;">
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

        {{-- Customer Selector (Button Trigger) --}}
        <div class="mb-3">
            <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Customer</label>

            <button
                class="btn btn-light border w-100 py-2 d-flex align-items-center justify-content-between shadow-sm rounded-3 bg-white customer-trigger-btn"
                onclick="openCustomerModal()">
                <div class="d-flex align-items-center overflow-hidden">
                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-2 flex-shrink-0"
                        style="width: 32px; height: 32px;">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="d-flex flex-column align-items-start text-truncate">
                        <span class="fw-bold text-dark lh-1 text-truncate selected-customer-name">Walk-in
                            Customer</span>
                        <small class="text-muted" style="font-size: 0.7rem;">Select Profile</small>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-muted small ms-2"></i>
            </button>

            {{-- Hidden Input for Logic Compatibility --}}
            <input type="hidden" class="customer-id-input" value="walk-in">
        </div>

        {{-- Totals --}}
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-secondary fw-medium">Subtotal</span>
            <span class="fw-bold text-dark">₱<span class="subtotal-display">0.00</span></span>
        </div>

        {{-- [PHASE 11] Discount Row --}}
        <div id="discount-row" class="d-flex justify-content-between align-items-center mb-1 text-success"
            style="display:none !important;">
            <span class="fw-medium small"><i class="fas fa-tags me-1"></i>Discount (<span
                    id="discount-label"></span>)</span>
            <span class="fw-bold small">- ₱<span id="discount-amount-display">0.00</span></span>
        </div>

        <div class="tax-row d-flex justify-content-between align-items-center mb-2" style="display:none;">
            <span class="text-secondary small">VAT (12%)</span>
            <span class="fw-bold text-dark small">₱<span class="tax-display">0.00</span></span>
        </div>

        {{-- [PHASE 11] Discount Button --}}
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="openDiscountModal()"
                id="btn-add-discount">
                <i class="fas fa-percent me-1"></i> Add Discount
            </button>
            <button class="btn btn-sm btn-outline-danger rounded-pill px-3 d-none" onclick="removeDiscount()"
                id="btn-remove-discount">
                <i class="fas fa-times me-1"></i> Remove Discount
            </button>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 pt-3 border-top">
            <span class="fw-bold text-dark h5 mb-0">Total</span>
            <span class="fw-bolder text-primary h3 mb-0">₱<span class="total-amount-display">0.00</span></span>
        </div>

        {{-- Checkout Button --}}
        <button
            class="btn btn-primary w-100 py-3 rounded-3 fw-bold shadow-sm d-flex justify-content-between align-items-center px-4"
            onclick="openPaymentModal()">
            <span>CHARGE</span>
            <span><i class="fas fa-arrow-right"></i></span>
        </button>
    </div>
</div>

<style>
    /* Custom Scrollbar for Cart */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
</style>