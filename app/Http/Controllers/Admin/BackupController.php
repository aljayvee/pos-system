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
        // Fix: Use config() instead of env() for production compatibility
        $dbName = config('database.connections.mysql.database');
        $filename = 'backup_' . $dbName . '_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';

        $headers = [
            "Content-type"        => "application/sql",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Stream the response to avoid memory issues with large databases
        $callback = function() use ($dbName) {
            $handle = fopen('php://output', 'w');

            // Disable Foreign Key Checks temporarily
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            // Get All Tables
            $tables = DB::select('SHOW TABLES');
            $tableKey = "Tables_in_" . $dbName;

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

    // 2. Restore Database (DISABLED FOR SECURITY)
    public function restore(Request $request)
    {
        // SECURITY FIX: This feature allows arbitrary SQL execution. 
        // We are disabling it to prevent RCE/Data Loss.
        return back()->with('error', 'Security Alert: Database restoration via Web UI is disabled. Please contact your system administrator to perform a manual restore.');
    }
}