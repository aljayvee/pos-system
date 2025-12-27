<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Pricing\PricingService;
use App\Models\Product;
use App\Models\ProductPricingTier;
use Illuminate\Database\Eloquent\Collection;

class PricingServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PricingService();
    }

    public function test_calculate_total_standard_price_no_tiers()
    {
        // Mock Product without tiers
        $product = $this->partialMock(Product::class, function ($mock) {
            $mock->shouldReceive('relationLoaded')->with('pricingTiers')->andReturn(true);
        });
        $product->price = 100.00;
        $product->setRelation('pricingTiers', new Collection());

        $total = $this->service->calculateTotal($product, 5);

        $this->assertEquals(500.00, $total);
    }

    public function test_calculate_total_exact_bundle_match()
    {
        // Setup: Buy 3 for 250 (Save 50)
        $tier = new ProductPricingTier(['quantity' => 3, 'price' => 250.00]);
        
        $product = $this->partialMock(Product::class, function ($mock) {
            $mock->shouldReceive('relationLoaded')->with('pricingTiers')->andReturn(true);
        });
        $product->price = 100.00;
        $product->setRelation('pricingTiers', new Collection([$tier]));

        $total = $this->service->calculateTotal($product, 3);

        $this->assertEquals(250.00, $total);
    }

    public function test_calculate_total_bundle_plus_remainder()
    {
        // Setup: Buy 3 for 250. We buy 4. Expected: 250 + 100 = 350.
        $tier = new ProductPricingTier(['quantity' => 3, 'price' => 250.00]);
        
        $product = $this->partialMock(Product::class, function ($mock) {
            $mock->shouldReceive('relationLoaded')->with('pricingTiers')->andReturn(true);
        });
        $product->price = 100.00;
        $product->setRelation('pricingTiers', new Collection([$tier]));

        $total = $this->service->calculateTotal($product, 4);

        $this->assertEquals(350.00, $total);
    }

    public function test_calculate_total_multiple_tiers_greedy()
    {
        // Setup: 
        // Unit: 100
        // Tier 1: Buy 5 for 400
        // Tier 2: Buy 2 for 180
        // Qty: 8 -> Expected: 1x(5) + 1x(2) + 1x(1) = 400 + 180 + 100 = 680
        
        $tier1 = new ProductPricingTier(['quantity' => 5, 'price' => 400.00]);
        $tier2 = new ProductPricingTier(['quantity' => 2, 'price' => 180.00]);
        
        $product = $this->partialMock(Product::class, function ($mock) {
            $mock->shouldReceive('relationLoaded')->with('pricingTiers')->andReturn(true);
        });
        $product->price = 100.00;
        $product->setRelation('pricingTiers', new Collection([$tier1, $tier2]));

        $total = $this->service->calculateTotal($product, 8);

        $this->assertEquals(680.00, $total);
    }
}
