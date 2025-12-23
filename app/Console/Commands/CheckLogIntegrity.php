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

        if ($result === true) {
            $this->info('✅ Chain is Valid. All logs are secure.');
            return 0;
        } else {
            $this->error('❌ TAMPERING DETECTED at Log ID: ' . $result);
            $this->warn('The chain is broken from this point onwards.');
            return 1;
        }
    }
}
