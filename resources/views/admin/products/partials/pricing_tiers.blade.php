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
                    <div class="row g-2 align-items-center mb-2 tier-row">
                        <div class="col-4">
                            <div class="form-floating form-floating-custom">
                                <input type="number" name="tiers[{{ $index }}][quantity]" class="form-control bg-light border-0 fw-bold" value="{{ $tier->quantity }}" placeholder="Qty" required min="2">
                                <label>Qty (e.g. 2)</label>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="form-floating form-floating-custom">
                                <input type="number" step="0.01" name="tiers[{{ $index }}][price]" class="form-control bg-light border-0 fw-bold" value="{{ $tier->price }}" placeholder="Price" required min="0">
                                <label>Total Price (e.g. 5.00)</label>
                            </div>
                        </div>
                        <div class="col-3">
                            <button type="button" class="btn btn-outline-danger w-100 h-100" onclick="removeTier(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <input type="hidden" name="tiers[{{ $index }}][name]" value="Multi-Buy">
                    </div>
                @endforeach
            @endif
        </div>

        <button type="button" class="btn btn-light border w-100 fw-bold text-secondary mt-2" onclick="addTier()">
            <i class="fas fa-plus-circle me-1"></i> Add Pricing Tier
        </button>
    </div>
</div>

<script>
    let tierIndex = {{ isset($product) ? $product->pricingTiers->count() : 0 }};

    function addTier() {
        const container = document.getElementById('pricing-tiers-container');
        const html = `
            <div class="row g-2 align-items-center mb-2 tier-row">
                <div class="col-4">
                    <div class="form-floating form-floating-custom">
                        <input type="number" name="tiers[${tierIndex}][quantity]" class="form-control bg-light border-0 fw-bold" placeholder="Qty" required min="2">
                        <label>Qty (e.g. 2)</label>
                    </div>
                </div>
                <div class="col-5">
                    <div class="form-floating form-floating-custom">
                        <input type="number" step="0.01" name="tiers[${tierIndex}][price]" class="form-control bg-light border-0 fw-bold" placeholder="Price" required min="0">
                        <label>Total Price</label>
                    </div>
                </div>
                <div class="col-3">
                    <button type="button" class="btn btn-outline-danger w-100 h-100" onclick="removeTier(this)">
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
