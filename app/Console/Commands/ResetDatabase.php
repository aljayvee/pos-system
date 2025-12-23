<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-data {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate all tables except users and system tables.';

    /**
     * Tables that should NEVER be truncated.
     */
    protected $excludedTables = [
        'users',
        'migrations',
        'failed_jobs',
        'password_reset_tokens',
        'personal_access_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'settings', // Usually want to keep settings
        'roles',    // If you have roles/permissions, keep them
        'permissions',
        'role_has_permissions',
        'model_has_roles',
        'model_has_permissions',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('⚠️  DANGER: This will delete ALL data (Products, Sales, Customers, etc) except Users. Are you sure?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $this->info('Processing database reset...');

        // Disable Foreign Key Checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = DB::select('SHOW TABLES');
        $dbName = 'Tables_in_' . env('DB_DATABASE', 'pos_db'); // Fallback or dynamic check
        
        // Handle "Tables_in_dbname" dynamic key
        $tables = array_map(function ($start) {
            return array_values((array)$start)[0];
        }, $tables);

        foreach ($tables as $table) {
            if (in_array($table, $this->excludedTables)) {
                $this->line("Skipping protected table: <comment>{$table}</comment>");
                continue;
            }

            $this->warn("Truncating: {$table}");
            try {
                DB::table($table)->truncate();
            } catch (\Exception $e) {
                // Determine if it's a "view" or actual error
                $this->error("Could not truncate {$table}: " . $e->getMessage());
            }
        }

        // Re-enable Foreign Key Checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('✅ Database reset complete. Users and Settings preserved.');
    }
}
