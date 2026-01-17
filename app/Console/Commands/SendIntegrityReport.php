<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendIntegrityReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integrity:report';
    protected $description = 'Verify log integrity and email the report to admin';

    public function handle(\App\Services\LogIntegrityService $service)
    {
        $this->info('Verifying Integrity...');

        $report = $service->verifyChain();

        // 1. Check for specific Alert Email in .env
        $recipient = env('INTEGRITY_ALERT_EMAIL');

        // 2. Fallback to Database Admin
        if (!$recipient) {
            $admin = \App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::find(1);
            $recipient = $admin ? $admin->email : null;
        }

        if (!$recipient) {
            $this->error('No recipient found (Set INTEGRITY_ALERT_EMAIL in .env or create an Admin user).');
            return 1;
        }

        $this->info("Sending report to: {$recipient}");

        try {
            \Illuminate\Support\Facades\Mail::to($recipient)->send(new \App\Mail\LogIntegrityMail($report));
            $this->info('✅ Report Sent Successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
            // In production, we might log this explicitly
        }

        return 0;
    }
}
