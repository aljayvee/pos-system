<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset-sales {--reset-stock : Also reset all inventory stock to 100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate all transaction tables but keep Users, Products, and Customers.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('This will delete ALL SALES, SESSIONS, and LOGS. Users/Products will be safe. Are you sure?')) {
            return;
        }

        $this->info('Cleaning database...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Transactional Tables (Delete Data)
        $tables = [
            'sales',
            'sale_items',
            'sales_returns',
            'customer_credits',
            'credit_payments',
            'cash_register_sessions',
            'cash_register_adjustments',
            'stock_adjustments',
            'activity_logs',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->line("Truncated: $table");
            }
        }

        // 2. Optional: Reset Inventory
        if ($this->option('reset-stock')) {
            if (Schema::hasTable('inventories')) {
                DB::table('inventories')->update(['stock' => 100]);
                $this->info('All inventory stock reset to 100.');
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Database cleaned! You can now start fresh testing.');
        $this->info('Users, Products, Categories, and Customers were PRESERVED.');
    }
}
