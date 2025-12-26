<?php

namespace App\Services\Pricing\Strategies;

use App\Services\Pricing\PricingStrategy;
use Illuminate\Support\Collection;

class MultiBuyStrategy implements PricingStrategy
{
    public function calculate(float $unitPrice, int $quantity, Collection $tiers): float
    {
        // 1. Sort tiers by quantity descending (Greedy approach: biggest bundles first)
        $sortedTiers = $tiers->sortByDesc('quantity');

        $remainingQty = $quantity;
        $totalPrice = 0.0;

        foreach ($sortedTiers as $tier) {
            if ($remainingQty >= $tier->quantity) {
                // How many of this bundle can we fit?
                $numBundles = floor($remainingQty / $tier->quantity);
                
                // Add price for these bundles
                $totalPrice += $numBundles * $tier->price;
                
                // Deduct the quantity covered
                $remainingQty %= $tier->quantity;
            }
        }

        // 2. Add remaining individual items at standard unit price
        if ($remainingQty > 0) {
            $totalPrice += $remainingQty * $unitPrice;
        }

        return $totalPrice;
    }
}
