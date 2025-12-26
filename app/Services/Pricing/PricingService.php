<?php

namespace App\Services\Pricing;

use App\Models\Product;
use App\Services\Pricing\Strategies\MultiBuyStrategy;

class PricingService
{
    protected PricingStrategy $strategy;

    public function __construct()
    {
        // For now, we hardcode MultiBuyStrategy. 
        // In the future, this could be resolved dynamically based on product settings.
        $this->strategy = new MultiBuyStrategy();
    }

    /**
     * Calculate the total price for a product quantity.
     */
    public function calculateTotal(Product $product, int $quantity): float
    {
        // Ensure tiers are loaded
        if (!$product->relationLoaded('pricingTiers')) {
            $product->load('pricingTiers');
        }

        return $this->strategy->calculate(
            $product->price,
            $quantity,
            $product->pricingTiers
        );
    }
}
