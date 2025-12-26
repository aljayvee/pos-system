<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CashRegisterService;
use App\Models\CashRegisterSession;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class CashRegisterServiceTest extends TestCase
{
    // We can't use RefreshDatabase easily without proper env config in this agent context,
    // so we will simulate logic or use a transaction rollback manually if possible.
    // For safety, we will mock the dependencies or use the existing DB if safe.
    // Given the live DB, we should be careful. 
    
    // STRATEGY: Create a dedicated TEST STORE ID to avoid polluting real data.
    
    protected $service;
    protected $storeId = 999; 

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CashRegisterService();
    }

    public function test_open_session_creates_record()
    {
        DB::beginTransaction();
        try {
            $user = User::first(); 
            $session = $this->service->openSession($this->storeId, $user->id, 500.00);

            $this->assertDatabaseHas('cash_register_sessions', [
                'store_id' => $this->storeId,
                'opening_amount' => 500.00,
                'status' => 'open'
            ]);

            return $session;
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        } finally {
            DB::rollBack();
        }
    }

    public function test_calculate_expected_cash()
    {
        DB::beginTransaction();
        try {
            $user = User::first();
            
            // 1. Open Session
            $session = $this->service->openSession($this->storeId, $user->id, 1000.00);

            // 2. Simulate Sale (Directly in DB to bypass POS locks for speed)
            // We use 'created_at' inside the session window
            $sale = Sale::create([
                'reference_number' => 'TEST-001',
                'store_id' => $this->storeId,
                'user_id' => $user->id,
                'customer_id' => null,
                'total_amount' => 250.00,
                'payment_method' => 'cash',
                'amount_tendered' => 300,
                'change' => 50,
                'created_at' => now()->addMinute()
            ]);

            // 3. Calculate
            $expected = $this->service->calculateExpectedCash($session);

            // Opening (1000) + Cash Sale (250) = 1250
            $this->assertEquals(1250.00, $expected, "Expected cash calculation failed. Got: $expected");

        } finally {
            DB::rollBack();
        }
    }
}
