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
            $this->warn('The chain is broken from Log ID: ' . ($result['log_id'] ?? 'Unknown'));
            return 1;
        }
    }
}
