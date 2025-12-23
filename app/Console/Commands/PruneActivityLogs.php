<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PruneActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:prune {--days=7 : Number of days to retain navigation logs}';
    protected $description = 'Prune old activity logs to save storage space';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Pruning 'Navigation' logs older than {$cutoffDate->toDateTimeString()}...");

        // Only delete "Navigation" entries. Critical actions are kept forever (or separate policy).
        // Since ActivityLog uses "Action" column roughly as category or specific action.
        // My middleware used 'action' => 'Navigation'.
        
        $count = \App\Models\ActivityLog::where('action', 'Navigation')
                    ->where('created_at', '<', $cutoffDate)
                    ->delete();

        $this->info("Deleted {$count} old log entries.");
        
        // Optimize table?
        // \Illuminate\Support\Facades\DB::statement('OPTIMIZE TABLE activity_logs'); // Optional, MySQL specific
        
        return 0;
    }
}
