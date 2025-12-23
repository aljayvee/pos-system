<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class LogIntegrityService
{
    /**
     * Generate a cryptographic hash for a log entry.
     * Consist of: User ID + Action + Description + CreatedAt + Previous Hash + App Key
     */
    public function generateHash(ActivityLog $log): string
    {
        // Get the latest log to find the previous hash
        // Note: In a race condition (high concurrency), this might pick the wrong previous hash.
        // For a single-store POS, strict serialization is usually acceptable, or we use a lock.
        // For now, we fetch the latest record BEFORE this one.
        
        $latest = ActivityLog::latest('id')->first();
        $previousHash = $latest ? $latest->hash : 'GENESIS_BLOCK';

        $dataToSign = implode('|', [
            $log->user_id ?? 'SYSTEM',
            $log->action,
            $log->description ?? '',
            now()->toIso8601String(), // We need a timestamp, but the model might not have one yet.
            $previousHash,
            config('app.key')
        ]);

        return hash('sha256', $dataToSign);
    }
    
    /**
     * Verify the entire chain of logs.
     * Returns true if valid, or the ID of the first tampered record.
     */
    public function verifyChain(): bool|int
    {
        $logs = ActivityLog::orderBy('id')->cursor();
        $previousHash = 'GENESIS_BLOCK';

        foreach ($logs as $log) {
            // Reconstruct data
            $dataToSign = implode('|', [
                $log->user_id ?? 'SYSTEM',
                $log->action,
                $log->description ?? '',
                $log->created_at->toIso8601String(),
                $previousHash,
                config('app.key')
            ]);
            
            $calculatedHash = hash('sha256', $dataToSign);

            if ($calculatedHash !== $log->hash) {
                // Return the ID of the broken link
                return $log->id;
            }

            $previousHash = $log->hash;
        }

        return true;
    }
}
