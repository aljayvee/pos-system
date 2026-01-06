<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\CustomerCredit;
use App\Models\Sale;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreditTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);

        // Mock Setup Complete
        \Illuminate\Support\Facades\DB::table('settings')->updateOrInsert(
            ['store_id' => 1, 'key' => 'setup_complete'],
            ['value' => '1']
        );
    }

    public function test_credits_index_loads_and_pay_link_renders()
    {
        // specific check: ensure we have credits so the loop runs
        $customer = Customer::create([
            'store_id' => 1,
            'name' => 'Test Customer',
            'contact' => '09123456789',
            'address' => 'Test Address',
            'points' => 0
        ]);

        $sale = Sale::create([
            'store_id' => 1,
            'user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'total_amount' => 1000,
            'amount_paid' => 0,
            'payment_method' => 'credit',
            'reference_number' => 'REF-' . uniqid()
        ]);

        $credit = CustomerCredit::create([
            'customer_id' => $customer->id,
            'sale_id' => $sale->id,
            'remaining_balance' => 1000,
            'total_amount' => 1000,
            'amount_paid' => 0,
            'is_paid' => 0
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['mpin_verified' => true])
            ->get(route('credits.index'));

        $response->assertStatus(200);
        $response->assertSee(route('credits.pay_form', $credit->credit_id));
    }

    public function test_payment_page_loads()
    {
        $customer = Customer::create([
            'store_id' => 1,
            'name' => 'Test Customer 3',
            'contact' => '09123456789',
            'address' => 'Test Address',
            'points' => 0
        ]);

        $sale = Sale::create([
            'store_id' => 1,
            'user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'total_amount' => 1000,
            'amount_paid' => 0,
            'payment_method' => 'credit',
            'reference_number' => 'REF-' . uniqid()
        ]);

        $credit = CustomerCredit::create([
            'customer_id' => $customer->id,
            'sale_id' => $sale->id,
            'remaining_balance' => 1000,
            'total_amount' => 1000,
            'amount_paid' => 0,
            'is_paid' => 0
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['mpin_verified' => true])
            ->get(route('credits.pay_form', $credit->credit_id));

        $response->assertStatus(200);
        $response->assertSee('Record Payment');
        $response->assertSee($customer->name);
    }

    public function test_payment_submission_works()
    {
        $customer = Customer::create([
            'store_id' => 1,
            'name' => 'Test Customer 2',
            'contact' => '09987654321',
            'address' => 'Test Address 2',
            'points' => 0
        ]);

        $sale = Sale::create([
            'store_id' => 1,
            'user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'total_amount' => 500,
            'amount_paid' => 0,
            'payment_method' => 'credit',
            'reference_number' => 'REF-' . uniqid()
        ]);

        $credit = CustomerCredit::create([
            'customer_id' => $customer->id,
            'sale_id' => $sale->id,
            'remaining_balance' => 500,
            'total_amount' => 500,
            'amount_paid' => 0,
            'is_paid' => 0
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['mpin_verified' => true])
            ->post(route('credits.pay', $credit->credit_id), [
                'amount' => 200,
                'notes' => 'Test Payment'
            ]);

        $response->assertSessionHas('success');
        $response->assertStatus(302); // Redirect back

        $this->assertDatabaseHas('customer_credits', [
            'credit_id' => $credit->credit_id,
            'remaining_balance' => 300 // 500 - 200
        ]);
    }
}
