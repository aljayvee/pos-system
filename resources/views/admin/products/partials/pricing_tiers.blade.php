<div class="card shadow-sm border-0 rounded-0 rounded-lg-4 mb-3 mb-lg-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-tags me-2 text-info"></i>Multi-Buy Pricing (Quantity Discounts)</h6>
    </div>
    <div class="card-body p-3 p-lg-4">
        <p class="text-muted small">Set special prices for bulk purchases (e.g., Buy 2 for ₱5.00).</p>
        
        <div id="pricing-tiers-container">
            {{-- Existing Tiers (for Edit) --}}
            @if(isset($product) && $product->pricingTiers->count() > 0)
                @foreach($product->pricingTiers as $index => $tier)
                    <div class="row g-2 align-items-end mb-3 tier-row">
                        <div class="col-4">
                            <label class="form-label fw-bold small text-secondary">Qty</label>
                            <input type="number" name="tiers[{{ $index }}][quantity]" class="form-control bg-light border-0 fw-bold py-3" value="{{ $tier->quantity }}" placeholder="e.g. 2" required min="2">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-secondary">Total Price</label>
                            <div class="input-group shadow-sm rounded-3 overflow-hidden border-0">
                                <span class="input-group-text bg-light border-0 text-muted">₱</span>
                                <input type="number" step="0.01" name="tiers[{{ $index }}][price]" class="form-control bg-light border-0 fw-bold py-3" value="{{ $tier->price }}" placeholder="0.00" required min="0">
                            </div>
                        </div>
                        <div class="col-2">
                             <button type="button" class="btn btn-outline-danger w-100 py-3 fw-bold rounded-3" onclick="removeTier(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <input type="hidden" name="tiers[{{ $index }}][name]" value="Multi-Buy">
                    </div>
                @endforeach
            @endif
        </div>

        <button type="button" class="btn btn-light border w-100 fw-bold text-secondary mt-2 py-3" onclick="addTier()">
            <i class="fas fa-plus-circle me-1"></i> Add Pricing Tier
        </button>
    </div>
</div>

<script>
    let tierIndex = {{ isset($product) ? $product->pricingTiers->count() : 0 }};

    function addTier() {
        const container = document.getElementById('pricing-tiers-container');
        const html = `
            <div class="row g-2 align-items-end mb-3 tier-row">
                <div class="col-4">
                    <label class="form-label fw-bold small text-secondary">Qty</label>
                    <input type="number" name="tiers[${tierIndex}][quantity]" class="form-control bg-light border-0 fw-bold py-3" placeholder="e.g. 2" required min="2">
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small text-secondary">Total Price</label>
                    <div class="input-group shadow-sm rounded-3 overflow-hidden border-0">
                        <span class="input-group-text bg-light border-0 text-muted">₱</span>
                        <input type="number" step="0.01" name="tiers[${tierIndex}][price]" class="form-control bg-light border-0 fw-bold py-3" placeholder="0.00" required min="0">
                    </div>
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-outline-danger w-100 py-3 fw-bold rounded-3" onclick="removeTier(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <input type="hidden" name="tiers[${tierIndex}][name]" value="Multi-Buy">
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        tierIndex++;
    }

    function removeTier(btn) {
        btn.closest('.tier-row').remove();
    }
</script>
