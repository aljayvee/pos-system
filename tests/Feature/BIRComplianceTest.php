<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Category;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\CashRegisterSession;
use Illuminate\Support\Facades\DB;

class BIRComplianceTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $store;
    protected $category;
    protected $session;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Store
        $this->store = Store::create([
            'name' => 'BIR Logic Store',
            'location' => 'Makati',
            'contact_number' => '09123456789',
        ]);

        // 2. Setup User
        $this->admin = User::firstOrCreate(
            ['email' => 'admin@bir.test'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'store_id' => $this->store->id,
                'email_verified_at' => now(),
            ]
        );
        $this->actingAs($this->admin);

        // 3. Setup Category & Products
        $this->category = Category::create(['name' => 'General']);

        // 4. Configure BIR Settings
        $this->configureBirSettings();

        // 5. Open Register
        $this->session = CashRegisterSession::create([
            'store_id' => $this->store->id,
            'user_id' => $this->admin->id,
            'opened_at' => now(),
            'opening_amount' => 1000,
            'status' => 'open'
        ]);
    }

    protected function configureBirSettings()
    {
        // Global Feature Flag (Config override for test)
        // Note: Config::set is temporary for request usually, but valid in test scope
        config(['safety_flag_features.bir_tax_compliance' => true]);

        // Key Database Settings
        DB::table('settings')->insert([
            ['store_id' => $this->store->id, 'key' => 'store_tin', 'value' => '123-456-789-000'],
            ['store_id' => $this->store->id, 'key' => 'store_name', 'value' => 'BIR Logic Store'],
            ['store_id' => $this->store->id, 'key' => 'tax_type', 'value' => 'inclusive'],
            ['store_id' => $this->store->id, 'key' => 'tax_rate', 'value' => '0.12'],
        ]);

        // Ensure Multi-Store is ON to avoid ID confusion
        DB::table('settings')->updateOrInsert(
            ['store_id' => 1, 'key' => 'enable_multi_store'],
            ['value' => '1']
        );
    }

    public function test_end_to_end_bir_flow()
    {
        // A. Setup Inventory
        $prod = Product::create([
            'store_id' => $this->store->id,
            'name' => 'Vatable Test',
            'price' => 112.00,
            'category_id' => $this->category->id,
            'sku' => 'VAT-TEST',
            'tax_type' => 'vatable',
            'unit' => 'pc'
        ]);
        Inventory::create(['store_id' => $this->store->id, 'product_id' => $prod->id, 'stock' => 100]);

        // B. Perform Sale (Compliant Mode)
        $cart = [
            ['id' => $prod->id, 'qty' => 1, 'price' => 112.00],
        ];

        $response = $this->withSession(['mpin_verified' => true, 'active_store_id' => $this->store->id])
            ->postJson(route('cashier.store'), [
                'cart' => $cart,
                'payment_method' => 'cash',
                'amount_paid' => 120.00,
                'customer_id' => 'walk-in'
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'sale_id']);

        // Check if correct view is returned (implied by content logic)
        $this->assertDatabaseHas('sales', [
            'store_id' => $this->store->id,
            'vatable_sales' => 100.00,
            'vat_amount' => 12.00,
        ]);

        // Check Electronic Journal for SALE
        $this->assertDatabaseHas('electronic_journals', [
            'store_id' => $this->store->id,
            'type' => 'INVOICE'
        ]);

        // C. Close Register (Trigger Z-Reading)
        $closeResponse = $this->withSession(['mpin_verified' => true, 'active_store_id' => $this->store->id])
            ->postJson('/cashier/register/close', [
                'session_id' => $this->session->id,
                'closing_amount' => 1112.00, // 1000 + 112
                'notes' => 'End of Day'
            ]);

        $closeResponse->assertStatus(200);

        // Assert Response contains Z-Reading data
        $closeResponse->assertJsonStructure([
            'success',
            'z_reading' => [
                'z_counter',
                'gross_sales',
                'vatable_sales',
                'old_grand_total',
                'new_grand_total'
            ]
        ]);

        $zData = $closeResponse->json('z_reading');
        $this->assertEquals(112.00, $zData['gross_sales']);
        $this->assertEquals(12.00, $zData['vat_amount']);

        // Check Store Accumulators
        $this->store->refresh();
        $this->assertEquals(112.00, $this->store->accumulated_grand_total);
        $this->assertEquals(1, $this->store->z_reading_counter);

        // Check Electronic Journal for Z-READING
        $this->assertDatabaseHas('electronic_journals', [
            'store_id' => $this->store->id,
            'type' => 'Z-READING'
        ]);
    }
}
