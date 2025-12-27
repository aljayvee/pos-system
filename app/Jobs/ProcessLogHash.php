<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessLogHash implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * We don't want infinite retries if it breaks integrity.
     */
    public $tries = 3;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Safety Check: Stop if feature disabled
        if (!config('safety_flag_features.log_integrity')) {
            return;
        }
        // Use an Atomic Lock to ensure only one worker processes hashes at a time.
        // This is crucial for maintaining the "Blockchain" sequence `previous_hash`
        // "log_hashing" is the lock key, 10 seconds timeout.
        Cache::lock('log_hashing', 10)->get(function () {
            
            // 1. Fetch all logs that have NO hash yet, ordered by ID
            // We process in a batch to handle high volume bursts efficiently
            $pendingLogs = ActivityLog::whereNull('hash')
                                      ->orderBy('id', 'asc')
                                      ->get();

            if ($pendingLogs->isEmpty()) {
                return;
            }

            foreach ($pendingLogs as $log) {
                // Double check inside loop (though lock should prevent race)
                if ($log->hash) continue;

                // 2. Find Previous Hash
                // We must get the absolute latest *hashed* record before this one
                $lastLog = ActivityLog::whereNotNull('hash')
                                      ->where('id', '<', $log->id)
                                      ->orderBy('id', 'desc')
                                      ->first();
                
                $previousHash = $lastLog ? $lastLog->hash : 'GENESIS_BLOCK';

                // 3. Generate Chain Hash
                $dataToSign = implode('|', [
                    $log->user_id ?? 'SYSTEM',
                    $log->action,
                    $log->description ?? '',
                    $log->created_at->toIso8601String(),
                    $previousHash,
                    config('app.key')
                ]);

                $log->previous_hash = $previousHash;
                $log->hash = hash('sha256', $dataToSign);
                $log->saveQuietly(); // Use saveQuietly to avoid triggering events recursively
            }
        });
    }
}
