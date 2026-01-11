<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class LogIntegrityService
{
    /**
     * Generate a cryptographic hash for a log entry using HMAC-SHA256.
     * Consist of: User ID + Action + Description + CreatedAt (Y-m-d H:i:s) + Previous Hash
     */
    public function generateHash(ActivityLog $log): string
    {
        // Get the latest log to find the previous hash if not already set (re-verification style)
        // But for creation, we need the *previous* record.
        // If the log is already saved, we might be verifying, so we don't look up "latest", we use previous logic?
        // Actually, for *creation*, we look up the latest existing record.

        // However, this function is used for BOTH creation and verification.
        // If creating: log->id is null (or we treat it as new).
        // If verifying: we need the previous hash from the chain.
        // To keep it simple, this function should purely sign the DATA provided in the model.
        // It's the caller's responsibility to set 'previous_hash' on the model correctly before calling this, 
        // OR we pass previousHash as an argument.

        // Let's rely on the model having 'previous_hash' populated if it's new, 
        // or we fetch it if missing? 
        // The safest way for verification is to pass the expected previous hash during the loop.

        // IMPROVED: We will signature the *current* state.

        $previousHash = $log->previous_hash ?? 'GENESIS_BLOCK';

        // Enforce Y-m-d H:i:s
        $timestamp = $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');

        $dataToSign = implode('|', [
            $log->user_id ?? 'SYSTEM',
            $log->action,
            $log->description ?? '',
            $timestamp,
            $previousHash
        ]);

        return hash_hmac('sha256', $dataToSign, config('app.key'));
    }

    /**
     * Verify the entire chain of logs.
     * Returns ['status' => 'OK'] or detailed failure info.
     */
    public function verifyChain(): array
    {
        // We cursor through logs specifically ordered by ID ASC
        $logs = ActivityLog::orderBy('id')->cursor();
        $previousHash = 'GENESIS_BLOCK';

        foreach ($logs as $log) {
            // Reconstruct data
            $timestamp = $log->created_at->format('Y-m-d H:i:s');

            $dataToSign = implode('|', [
                $log->user_id ?? 'SYSTEM',
                $log->action,
                $log->description ?? '',
                $timestamp,
                $previousHash
            ]);

            $calculatedHash = hash_hmac('sha256', $dataToSign, config('app.key'));

            // Check consistency
            // 1. Check if the stored hash matches calculation
            if ($calculatedHash !== $log->hash) {
                return [
                    'status' => 'TAMPERED',
                    'log_id' => $log->id,
                    'reason' => 'Hash Mismatch',
                    'expected_hash' => $calculatedHash,
                    'actual_hash' => $log->hash,
                    'user' => $log->user->name ?? 'System',
                    'date' => $timestamp
                ];
            }

            // 2. Check if the 'previous_hash' column matches what we have in memory
            // (If existing logs didn't store previous_hash, this might fail unless we migrated)
            // Assuming we added previous_hash column. If not, we skip this check or strictly verify via hash linking.
            if ($log->previous_hash && $log->previous_hash !== $previousHash) {
                return [
                    'status' => 'TAMPERED',
                    'log_id' => $log->id,
                    'reason' => 'Chain Broken (Previous Hash invalid)',
                    'expected_previous' => $previousHash,
                    'actual_previous' => $log->previous_hash
                ];
            }

            $previousHash = $log->hash;
        }

        return ['status' => 'OK'];
    }
}
