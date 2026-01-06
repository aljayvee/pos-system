<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Action;
use App\Models\Store;
use App\Models\Category;
use App\Models\Product;
use App\Models\Inventory;
use App\Enums\Permission;
use App\Models\CashRegisterSession;
use Illuminate\Support\Facades\DB;

class TaxLogicTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $store;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Store
        $this->store = Store::create([
            'name' => 'Test Store',
            'location' => 'Test Location', // Added required field if any
            'contact_number' => '09123456789',
            // 'tax_type' => 'inclusive', // Not a column on stores table? It's in settings.
            // Check Store migration. usually name, address, contact.
        ]);

        // Setup User
        $this->admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'store_id' => $this->store->id,
                'email_verified_at' => now(), // Fix for verification middleware
            ]
        );

        $this->actingAs($this->admin);

        // Setup Category
        $this->category = Category::create(['name' => 'General']);

        // Setup Settings
        DB::table('settings')->insert([
            ['store_id' => $this->store->id, 'key' => 'tax_type', 'value' => 'inclusive'],
            ['store_id' => $this->store->id, 'key' => 'tax_rate', 'value' => '0.12'],
        ]);

        // ENABLE BIR MODE for detailed tax logic
        config(['safety_flag_features.bir_tax_compliance' => true]);

        DB::table('settings')->updateOrInsert(
            ['store_id' => 1, 'key' => 'enable_multi_store'],
            ['value' => '1']
        );

        // Open Register
        CashRegisterSession::create([
            'store_id' => $this->store->id,
            'user_id' => $this->admin->id,
            'opened_at' => now(),
            'opening_amount' => 1000,
            'status' => 'open'
        ]);
    }

    public function test_per_product_tax_calculation()
    {
        // 1. Create Products with different tax types
        $prodVatable = Product::create([
            'store_id' => $this->store->id,
            'name' => 'Vatable Item',
            'price' => 112.00,
            'cost' => 80.00,
            'category_id' => $this->category->id,
            'sku' => 'VAT001',
            'tax_type' => 'vatable',
            'unit' => 'pc'
        ]);
        Inventory::create(['store_id' => $this->store->id, 'product_id' => $prodVatable->id, 'stock' => 100]);

        $prodExempt = Product::create([
            'store_id' => $this->store->id,
            'name' => 'Exempt Item',
            'price' => 100.00,
            'cost' => 80.00,
            'category_id' => $this->category->id,
            'sku' => 'EXE001',
            'tax_type' => 'vat_exempt',
            'unit' => 'pc'
        ]);
        Inventory::create(['store_id' => $this->store->id, 'product_id' => $prodExempt->id, 'stock' => 100]);

        $prodZero = Product::create([
            'store_id' => $this->store->id,
            'name' => 'Zero Rated Item',
            'price' => 100.00,
            'cost' => 80.00,
            'category_id' => $this->category->id,
            'sku' => 'ZERO001',
            'tax_type' => 'zero_rated',
            'unit' => 'pc'
        ]);
        Inventory::create(['store_id' => $this->store->id, 'product_id' => $prodZero->id, 'stock' => 100]);

        // 2. Prepare Cart
        $cart = [
            ['id' => $prodVatable->id, 'qty' => 1, 'price' => 112.00], // Tax: 12.00, Base: 100.00
            ['id' => $prodExempt->id, 'qty' => 1, 'price' => 100.00],  // Tax: 0, Base: 100.00
            ['id' => $prodZero->id, 'qty' => 1, 'price' => 100.00],    // Tax: 0, Base: 100.00
        ];

        // Debugging with dd
        // dd(Inventory::all()->toArray());

        // 3. Submit Sale
        $response = $this->withSession(['mpin_verified' => true, 'active_store_id' => $this->store->id])
            ->postJson(route('cashier.store'), [
                'cart' => $cart,
                'payment_method' => 'cash',
                'amount_paid' => 500.00,
                'customer_id' => 'walk-in'
            ]);

        $response->assertStatus(200);
        $saleId = $response->json('sale_id');

        // 4. Verify Database Records
        $this->assertDatabaseHas('sales', [
            'id' => $saleId,
            'total_amount' => 312.00,
            'vatable_sales' => 100.00,
            'vat_amount' => 12.00, // Fixed: logic might use output_vat or vat_amount, I used vat_amount in migration
            'vat_exempt_sales' => 100.00,
            'vat_zero_rated_sales' => 100.00,
        ]);
    }
}
