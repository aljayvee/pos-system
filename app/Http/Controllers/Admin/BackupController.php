<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ActivityLog; // <--- Add this at the top

class BackupController extends Controller
{
    // 1. Download Backup (.sql file)
    public function download()
    {
        $dbName = env('DB_DATABASE', 'sari_sari_store');
        $filename = 'backup_' . $dbName . '_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';

        $headers = [
            "Content-type"        => "application/sql",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Stream the response to avoid memory issues with large databases
        $callback = function() {
            $handle = fopen('php://output', 'w');

            // Disable Foreign Key Checks temporarily
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            // Get All Tables
            $tables = DB::select('SHOW TABLES');
            $tableKey = "Tables_in_" . env('DB_DATABASE');

            foreach ($tables as $table) {
                $tableName = $table->$tableKey ?? current((array)$table);

                // Skip migrations table to prevent conflicts
                if ($tableName == 'migrations' || $tableName == 'sessions') continue;

                fwrite($handle, "-- TABLE: $tableName\n");
                fwrite($handle, "DROP TABLE IF EXISTS `$tableName`;\n");

                // Get Create Table Statement
                $createRow = DB::select("SHOW CREATE TABLE `$tableName`");
                $createSql = $createRow[0]->{'Create Table'} . ";\n\n";
                fwrite($handle, $createSql);

                // Get Table Data
                $rows = DB::table($tableName)->get();
                foreach ($rows as $row) {
                    $values = array_map(function ($value) {
                        if (is_null($value)) return "NULL";
                        return "'" . addslashes($value) . "'";
                    }, (array)$row);

                    $sql = "INSERT INTO `$tableName` VALUES (" . implode(", ", $values) . ");\n";
                    fwrite($handle, $sql);
                }
                fwrite($handle, "\n");
            }

            // Re-enable Foreign Key Checks
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // 2. Restore Database
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file' 
        ]);

        $file = $request->file('backup_file');
        
        // Basic extension check
        if ($file->getClientOriginalExtension() !== 'sql') {
            return back()->with('error', 'Invalid file. Please upload a .sql file.');
        }

        try {
            $sql = file_get_contents($file->getRealPath());

            // Disable foreign key checks to allow dropping tables
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Execute the SQL commands
            DB::unprepared($sql);

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // LOG ACTION
            // Note: Since we just wiped the database, this log entry will be the *first* entry
            // in the newly restored 'activity_logs' table (or appended if you preserved logs).
            // Usually, restore wipes everything, so this marks the "Start" of the new timeline.
            ActivityLog::create([
                'user_id' => auth()->id() ?? 1, // Fallback to ID 1 if session is weird after restore
                'action' => 'System Restore',
                'description' => 'Restored database from backup file: ' . $file->getClientOriginalName()
            ]);

            return back()->with('success', 'Database restored successfully! Please log in again.');

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }
}