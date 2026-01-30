<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendWeeklyLogReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integrity:report';
    protected $description = 'Send weekly secure audit log report to admins and managers';

    public function handle()
    {
        $this->info('Generating Weekly Audit Log Report...');

        // 1. Fetch Logs (Last 7 Days)
        $startDate = now()->subDays(7)->startOfDay();
        $endDate = now()->endOfDay();
        $logs = \App\Models\ActivityLog::with('user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();

        if ($logs->isEmpty()) {
            $this->info('No logs found for this week.');
            return;
        }

        // 2. Get Recipients
        $recipients = \App\Models\User::with('mpin') // Eager load MPIN
            ->whereIn('role', ['admin', 'manager'])
            ->whereNotNull('email')
            ->get();

        $this->info("Found " . $recipients->count() . " recipients.");

        foreach ($recipients as $user) {
            $this->info("Processing for: {$user->name} ({$user->email})");

            // 3. Generate Password: Birthdate(YYYYMMDD) + Username + First Name
            $birthdate = $user->birthdate ? \Carbon\Carbon::parse($user->birthdate)->format('Ymd') : '00000000';
            $username = $user->username ?? 'user';
            $firstName = $user->first_name ?? '';

            $password = $birthdate . $username . $firstName;

            // 4. Generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.audit_log', [
                'logs' => $logs,
                'startDate' => $startDate->toDateString(),
                'endDate' => $endDate->toDateString()
            ]);

            // Set Encryption
            $pdf->setEncryption($password);

            // Save Temporary File
            $fileName = 'audit_log_' . $user->id . '_' . time() . '.pdf';
            $path = storage_path('app/temp/' . $fileName);

            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $pdf->save($path);

            // 5. Send Email
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\WeeklyAuditLog($path, $password));
                $this->info(" - Email sent successfully.");
            } catch (\Exception $e) {
                $this->error(" - Failed to send email: " . $e->getMessage());
            }

            // Cleanup
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $this->info('Weekly Report Process Completed.');
    }
}
