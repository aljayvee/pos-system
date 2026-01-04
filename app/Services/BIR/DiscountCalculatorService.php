<?php

namespace App\Services\BIR;

class DiscountCalculatorService
{
    /**
     * Calculate 20% SC/PWD Discount.
     * 
     * Formula:
     * 1. If Vatable: Net = Gross / 1.12
     * 2. If Exempt/Zero: Net = Gross
     * 3. Discount = Net * 0.20
     * 4. Final = Net - Discount
     */
    public function calculate(float $price, string $taxType, string $discountType, bool $isInclusive = true): array
    {
        $taxRate = 0.12;
        $vatRemoved = 0;
        $basePrice = $price;
        $isVatable = false;

        // 1. Remove VAT if applicable
        if ($taxType === 'vatable' || $taxType === 'inclusive') {
            // Logic Split:
            // If Inclusive: Price (112) includes Tax -> Base = 112 / 1.12 = 100.
            // If Exclusive: Price (100) is Base -> Base = 100. (No division).

            if ($isInclusive) {
                $basePrice = $price / (1 + $taxRate);
                $vatRemoved = $price - $basePrice;
            } else {
                $basePrice = $price;
                $vatRemoved = 0; // Tax wasn't in the price to begin with
            }
            $isVatable = true;
        }

        // 2. Apply 20% Discount on Base Price
        $discountAmount = $basePrice * 0.20;
        $finalTotal = $basePrice - $discountAmount;

        return [
            'gross_amount' => $price,
            'vat_removed' => $vatRemoved,      // To show how much VAT was saved
            'base_price' => $basePrice,        // The amount that goes to Exempt Sales
            'discount_amount' => $discountAmount, // The 20%
            'final_total' => $finalTotal,      // Payable
            'is_vatable_origin' => $isVatable  // Helper to know if we shifted classification
        ];
    }
}
