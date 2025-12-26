<?php

namespace App\Services\Pricing;

use Illuminate\Support\Collection;

interface PricingStrategy
{
    /**
     * Calculate the total price based on quantity and pricing tiers.
     *
     * @param float $unitPrice  The base unit price of the product
     * @param int $quantity     The quantity being purchased
     * @param Collection $tiers The pricing tiers for the product
     * @return float            The calculated total price
     */
    public function calculate(float $unitPrice, int $quantity, Collection $tiers): float;
}
