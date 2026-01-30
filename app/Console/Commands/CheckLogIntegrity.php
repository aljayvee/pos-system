<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LogIntegrityService;

class CheckLogIntegrity extends Command
{
    protected $signature = 'integrity:check';
    protected $description = 'Verify the cryptographic integrity of activity logs';

    public function handle(LogIntegrityService $service)
    {
        $this->info('Starting Integrity Check...');

        $result = $service->verifyChain();

        if ($result['status'] === 'OK') {
            $this->info('✅ Chain is Valid. All logs are secure.');
            return 0;
        } else {
            $this->error('❌ TAMPERING DETECTED');

            // 1. Display Table
            $this->table(
                ['Log ID', 'Reason', 'Expected', 'Actual'],
                [
                    [
                        $result['log_id'] ?? 'N/A',
                        $result['reason'] ?? 'Unknown',
                        substr($result['expected_hash'] ?? '', 0, 10) . '...',
                        substr($result['actual_hash'] ?? '', 0, 10) . '...'
                    ]
                ]
            );

            // 2. Alert Admins & Managers
            $recipients = \App\Models\User::whereIn('role', ['admin', 'manager'])
                ->whereNotNull('email')
                ->get();

            $this->info("Alerting " . $recipients->count() . " admins/managers...");

            foreach ($recipients as $user) {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\LogTampered($result));
                $this->line(" - Sent to: {$user->email}");
            }

            $this->warn('The chain is broken from Log ID: ' . ($result['log_id'] ?? 'Unknown'));
            return 1;
        }
    }
}
