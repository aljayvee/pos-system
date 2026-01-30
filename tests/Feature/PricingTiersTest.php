<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PricingTier;
use App\Models\User;
use App\Models\Store;
use App\Models\Inventory;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingTiersTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::create(['name' => 'Test Store']);

        // Enable Multi-Store so context switching works
        Setting::updateOrCreate(
            ['key' => 'enable_multi_store', 'store_id' => 1],
            ['value' => '1']
        );

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'store_id' => $this->store->id,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin_test'
        ]);
    }

    public function test_products_with_tiers_are_loaded_correctly_in_pos()
    {
        $category = Category::create(['name' => 'Test Cat', 'store_id' => $this->store->id]);

        $product = Product::create([
            'name' => 'Promo Item',
            'price' => 100.00,
            'sku' => 'PROMO123',
            'category_id' => $category->id,
            'store_id' => $this->store->id,
            'unit' => 'pc',
            'tax_type' => 'vatable'
        ]);

        Inventory::create([
            'product_id' => $product->id,
            'store_id' => $this->store->id,
            'stock' => 100
        ]);

        PricingTier::create([
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 250.00
        ]);

        $this->actingAs($this->admin);

        session([
            'active_store_id' => $this->store->id,
            'mpin_verified' => true
        ]);

        $url = route('cashier.pos');
        $response = $this->get($url);

        $response->assertStatus(200);

        $content = $response->getContent();

        // Debug if missing
        if (!str_contains($content, '"quantity":3')) {
            // Check if it's snake_case
            if (str_contains($content, '"quantity":3')) {
                // Good
            } else {
                dump("Missing quantity 3 in JSON");
                // dump(substr($content, strpos($content, 'ALL_PRODUCTS'), 1000));
            }
        }

        $this->assertStringContainsString('"quantity":3', $content);
        $this->assertStringContainsString('250', $content);
    }

    public function test_checkout_applies_pricing_tiers_on_backend()
    {
        $category = Category::create(['name' => 'Test Cat', 'store_id' => $this->store->id]);

        $product = Product::create([
            'name' => 'Promo Item',
            'price' => 100.00,
            'sku' => 'PROMO123',
            'category_id' => $category->id,
            'store_id' => $this->store->id,
            'unit' => 'pc',
            'tax_type' => 'vatable'
        ]);

        Inventory::create([
            'product_id' => $product->id,
            'store_id' => $this->store->id,
            'stock' => 100
        ]);

        PricingTier::create([
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 250.00
        ]);

        $this->actingAs($this->admin);
        session([
            'active_store_id' => $this->store->id,
            'mpin_verified' => true
        ]);

        $payload = [
            'cart' => [
                [
                    'id' => $product->id,
                    'qty' => 4, // 3@250 + 1@100 = 350
                    'price' => 100.00
                ]
            ],
            'payment_method' => 'cash',
            'amount_paid' => 400.00,
            'customer_id' => 'walk-in',
            'discount' => ['type' => 'none']
        ];

        $response = $this->postJson(route('cashier.store'), $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('sales', [
            'total_amount' => 350.00
        ]);
    }
}
