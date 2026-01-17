<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Category;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\CashRegisterSession;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ZReadingTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $store;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::create([
            'name' => 'Accumulator Test Store',
            'location' => 'Makati',
            'contact_number' => '09123456789',
        ]);

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@accumulator.test'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'store_id' => $this->store->id,
            ]
        );
        $this->actingAs($this->admin);

        $cat = Category::create(['name' => 'Gen']);
        $this->product = Product::create([
            'store_id' => $this->store->id,
            'name' => 'Item A',
            'price' => 100.00,
            'category_id' => $cat->id,
            'sku' => 'TEST-A',
            'tax_type' => 'inclusive', // 100 php price includes tax
            'unit' => 'pc'
        ]);
        Inventory::create(['store_id' => $this->store->id, 'product_id' => $this->product->id, 'stock' => 1000]);

        // Enable BIR
        config(['safety_flag_features.bir_tax_compliance' => true]);
        DB::table('settings')->insert([
            ['store_id' => $this->store->id, 'key' => 'store_tin', 'value' => '000'],
            ['store_id' => $this->store->id, 'key' => 'store_name', 'value' => 'Test'],
            ['store_id' => $this->store->id, 'key' => 'tax_type', 'value' => 'inclusive'],
            ['store_id' => $this->store->id, 'key' => 'tax_rate', 'value' => '0.12'],
        ]);

        // Update global multi-store setting
        DB::table('settings')->updateOrInsert(
            ['store_id' => 1, 'key' => 'enable_multi_store'],
            ['value' => '1']
        );

        // Start Time
        Carbon::setTestNow(Carbon::parse('2024-01-01 08:00:00'));
    }

    public function test_grand_total_accumulation_over_multiple_days()
    {
        // ==========================================
        // DAY 1: Sales = 200.00
        // Expected GT = 200.00
        // ==========================================
        $this->performDayCycle(200.00, 11200.00); // 1 = 1st Zreading

        $this->store->refresh();
        $this->assertEquals(200.00, $this->store->accumulated_grand_total, "Day 1 GT Mismatch");
        $this->assertEquals(1, $this->store->z_reading_counter, "Day 1 Counter Mismatch");


        // ==========================================
        // DAY 2: Sales = 300.00
        // Expected GT = 200 + 300 = 500.00
        // ==========================================
        Carbon::setTestNow(Carbon::parse('2024-01-02 08:00:00'));
        $this->performDayCycle(300.00, 12500.00); // 2 = 2nd Zreading

        $this->store->refresh();
        $this->assertEquals(500.00, $this->store->accumulated_grand_total, "Day 2 GT Mismatch");
        $this->assertEquals(2, $this->store->z_reading_counter, "Day 2 Counter Mismatch");


        // ==========================================
        // DAY 3: Sales = 150.00
        // Expected GT = 500 + 150 = 650.00
        // ==========================================
        Carbon::setTestNow(Carbon::parse('2024-01-03 08:00:00'));
        $this->performDayCycle(150.00, 13650.00);

        $this->store->refresh();
        $this->assertEquals(650.00, $this->store->accumulated_grand_total, "Day 3 GT Mismatch");
        $this->assertEquals(3, $this->store->z_reading_counter, "Day 3 Counter Mismatch");
    }

    private function performDayCycle($salesAmount, $closingCash)
    {
        // 1. Open Register
        $session = CashRegisterSession::create([
            'store_id' => $this->store->id,
            'user_id' => $this->admin->id,
            'opened_at' => now(),
            'opening_amount' => 1000,
            'status' => 'open'
        ]);

        // 2. Make Sale
        $qty = $salesAmount / 100; // Price is 100
        $cart = [['id' => $this->product->id, 'qty' => $qty, 'price' => 100.00]];

        $this->withSession(['mpin_verified' => true, 'active_store_id' => $this->store->id])
            ->postJson(route('cashier.store'), [
                'cart' => $cart,
                'payment_method' => 'cash',
                'amount_paid' => $salesAmount,
                'customer_id' => 'walk-in'
            ])->assertStatus(200);

        // 3. Close Register
        $this->withSession(['mpin_verified' => true, 'active_store_id' => $this->store->id])
            ->postJson('/cashier/register/close', [
                'session_id' => $session->id,
                'closing_amount' => $closingCash,
                'notes' => 'End of Day'
            ])
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'z_reading']);
    }
}
