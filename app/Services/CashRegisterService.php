<?php

namespace App\Services;

use App\Models\CashRegisterSession;
use App\Models\CashRegisterAdjustment;
use App\Models\Sale;
use App\Models\CreditPayment;
use App\Models\SalesReturn;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashRegisterService
{
    /**
     * Open a new Register Session
     */
    public function openSession(int $storeId, int $userId, float $openingAmount)
    {
        return DB::transaction(function() use ($storeId, $userId, $openingAmount) {
            // [ACID] Lock the STORE record to serialize access (prevent double open)
            // We lock the store itself or a related "config" to ensure only one process 
            // can check-and-create for this store at a time.
            \App\Models\Store::where('id', $storeId)->lockForUpdate()->first();

            // 1. Check if already open (Inside Lock)
            $existing = CashRegisterSession::where('store_id', $storeId)
                ->where('status', 'open')
                ->first();

            if ($existing) {
                throw new \Exception("Register is already open for this store.");
            }

            return CashRegisterSession::create([
                'store_id' => $storeId,
                'user_id' => $userId, // Admin who opened it
                'opening_amount' => $openingAmount,
                'status' => 'open',
                'opened_at' => now(),
            ]);
        });
    }

    /**
     * Get Current Active Session Logic
     */
    public function getCurrentSession(int $storeId)
    {
        return CashRegisterSession::where('store_id', $storeId)
            ->where('status', 'open')
            ->first();
    }

    /**
     * Calculate Expected Cash for a Session
     * Formula: Opening + Cash Sales + Cash Collections - Cash Returns
     */
    public function calculateExpectedCash(CashRegisterSession $session): float
    {
        $storeId = $session->store_id;
        $start = $session->opened_at;
        $end = $session->closed_at ?? now();

        // 1. Cash Sales (Created during this session window)
        $cashSales = Sale::where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        // 2. Debt Collections (Cash)
        $collections = CreditPayment::whereHas('credit.sale', function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->whereBetween('payment_date', [$start, $end]) // Assuming payment_date includes time or purely date?
            // Note: CreditPayment usually stores date. If strictly time-bound, might need 'created_at'.
            // For now, let's use created_at if available, or payment_date assumes day-bound.
            ->whereBetween('created_at', [$start, $end]) 
            ->sum('amount');

        // 3. Cash Refunds
        $refunds = SalesReturn::whereHas('sale', function($q) use ($storeId) {
                $q->where('store_id', $storeId)->where('payment_method', 'cash');
            })
            ->whereBetween('created_at', [$start, $end])
            ->sum('refund_amount');

        return $session->opening_amount + $cashSales + $collections - $refunds;
    }

    /**
     * Close a Session
     */
    public function closeSession(int $sessionId, float $closingActual, ?string $notes = null)
    {
        $session = CashRegisterSession::findOrFail($sessionId);
        
        if ($session->status === 'closed') {
            throw new \Exception("Session is already closed.");
        }

        // Set closing time specific to this action to freeze calculation
        $closingTime = now();
        $session->closed_at = $closingTime; // Important for calculateExpectedCash to be frozen

        $expected = $this->calculateExpectedCash($session);
        $variance = $closingActual - $expected;

        $session->update([
            'closing_amount' => $closingActual,
            'expected_amount' => $expected,
            'variance' => $variance,
            'status' => 'closed',
            'notes' => $notes
        ]);

        // [NEW] If there is a discrepancy, auto-create a Pending Adjustment for Admin review
        if (abs($variance) > 0.01) {
            CashRegisterAdjustment::create([
                'cash_register_session_id' => $session->id,
                'user_id' => $session->user_id, // Attribute to the session owner
                'original_amount' => $expected,
                'new_amount' => $closingActual,
                'reason' => "Register Closing Discrepancy (Variance: " . number_format($variance, 2) . ")" . ($notes ? " - Notes: $notes" : ""),
                'status' => 'pending'
            ]);
        }

        return $session;
    }

    /**
     * Request an Adjustment (Manager)
     */
    public function requestAdjustment(int $sessionId, int $userId, float $newAmount, string $reason)
    {
        $session = CashRegisterSession::findOrFail($sessionId);
        
        return CashRegisterAdjustment::create([
            'cash_register_session_id' => $session->id,
            'user_id' => $userId, // Requestor
            'original_amount' => $session->closing_amount,
            'new_amount' => $newAmount,
            'reason' => $reason,
            'status' => 'pending'
        ]);
    }

    /**
     * Process Adjustment (Admin)
     */
    public function processAdjustment(int $adjustmentId, int $adminId, string $action) {
        $adjustment = CashRegisterAdjustment::findOrFail($adjustmentId);
        
        if ($adjustment->status !== 'pending') {
            throw new \Exception("Request already processed.");
        }

        DB::transaction(function() use ($adjustment, $adminId, $action) {
            $adjustment->approved_by = $adminId;
            $adjustment->status = ($action === 'approve') ? 'approved' : 'rejected';
            $adjustment->save();

            if ($action === 'approve') {
                $session = $adjustment->session;
                
                // Recalculate Variance based on NEW closing amount
                $variance = $adjustment->new_amount - $session->expected_amount;

                $session->update([
                    'closing_amount' => $adjustment->new_amount,
                    'variance' => $variance,
                    'notes' => $session->notes . "\n[Amendment] Changed from {$adjustment->original_amount} to {$adjustment->new_amount}. Reason: {$adjustment->reason}"
                ]);
            }
        });

        return $adjustment;
    }
}
