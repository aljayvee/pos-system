<?php

namespace Tests\Unit\Pricing;

use App\Models\PricingTier;
use App\Services\Pricing\Strategies\MultiBuyStrategy;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class MultiBuyStrategyTest extends TestCase
{
    private MultiBuyStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new MultiBuyStrategy();
    }

    public function test_it_calculates_standard_price_when_no_tiers()
    {
        $tiers = new Collection();
        $unitPrice = 10.0;
        $quantity = 5;

        $result = $this->strategy->calculate($unitPrice, $quantity, $tiers);

        $this->assertEquals(50.0, $result);
    }

    public function test_it_applies_single_tier_exact_quantity()
    {
        $tiers = new Collection([
            new PricingTier(['quantity' => 10, 'price' => 50.0]) // 10 for 50 (5.0/unit instead of 10.0)
        ]);
        $unitPrice = 10.0;
        $quantity = 10;

        $result = $this->strategy->calculate($unitPrice, $quantity, $tiers);

        $this->assertEquals(50.0, $result);
    }

    public function test_it_applies_single_tier_multiple_times()
    {
        $tiers = new Collection([
            new PricingTier(['quantity' => 10, 'price' => 50.0])
        ]);
        $unitPrice = 10.0;
        $quantity = 20;

        $result = $this->strategy->calculate($unitPrice, $quantity, $tiers);

        $this->assertEquals(100.0, $result);
    }

    public function test_it_applies_tier_and_remainder()
    {
        $tiers = new Collection([
            new PricingTier(['quantity' => 10, 'price' => 50.0])
        ]);
        $unitPrice = 10.0;
        $quantity = 12; // 1 bundle (50) + 2 units (20) = 70

        $result = $this->strategy->calculate($unitPrice, $quantity, $tiers);

        $this->assertEquals(70.0, $result);
    }

    public function test_it_applies_multiple_tiers_greedy()
    {
        // 10 for 50
        // 5 for 30
        $tiers = new Collection([
            new PricingTier(['quantity' => 10, 'price' => 50.0]),
            new PricingTier(['quantity' => 5, 'price' => 30.0])
        ]);
        $unitPrice = 10.0;

        // Quantity 17 = 1x10 (50) + 1x5 (30) + 2x1 (20) = 100
        $quantity = 17;

        $result = $this->strategy->calculate($unitPrice, $quantity, $tiers);

        $this->assertEquals(100.0, $result);
    }

    public function test_it_handles_unordered_tiers()
    {
        $tiers = new Collection([
            new PricingTier(['quantity' => 5, 'price' => 30.0]),
            new PricingTier(['quantity' => 10, 'price' => 50.0])
        ]);
        $unitPrice = 10.0;

        // Quantity 17 = 1x10 (50) + 1x5 (30) + 2x1 (20) = 100
        $quantity = 17;

        $result = $this->strategy->calculate($unitPrice, $quantity, $tiers);

        $this->assertEquals(100.0, $result);
    }
}
