<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupController extends Controller
{
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

        $callback = function() {
            $handle = fopen('php://output', 'w');

            // 1. Get All Tables
            $tables = DB::select('SHOW TABLES');
            $tableKey = "Tables_in_" . env('DB_DATABASE');

            foreach ($tables as $table) {
                $tableName = $table->$tableKey ?? current((array)$table);

                // Skip migrations table to avoid conflicts on restore
                if ($tableName == 'migrations') continue;

                fwrite($handle, "\n\n" . "-- TABLE: $tableName" . "\n");
                fwrite($handle, "DROP TABLE IF EXISTS `$tableName`;" . "\n");

                // 2. Get Create Table Statement
                $createRow = DB::select("SHOW CREATE TABLE `$tableName`");
                $createSql = $createRow[0]->{'Create Table'} . ";\n";
                fwrite($handle, $createSql);

                // 3. Get Table Data
                $rows = DB::table($tableName)->get();
                
                foreach ($rows as $row) {
                    $values = array_map(function ($value) {
                        if (is_null($value)) return "NULL";
                        return "'" . addslashes($value) . "'";
                    }, (array)$row);

                    $sql = "INSERT INTO `$tableName` VALUES (" . implode(", ", $values) . ");\n";
                    fwrite($handle, $sql);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // NEW: Restore Database Logic
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file' // Allow sql/txt files
        ]);

        $file = $request->file('backup_file');
        
        // Basic check for .sql extension
        if ($file->getClientOriginalExtension() !== 'sql') {
            return back()->with('error', 'Invalid file format. Please upload a .sql file.');
        }

        try {
            // Read file content
            $sql = file_get_contents($file->getRealPath());

            // Disable Foreign Key Checks to avoid errors during drop/create
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Execute the raw SQL
            // Note: DB::unprepared is used for multiple statements
            DB::unprepared($sql);

            // Re-enable Foreign Key Checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return back()->with('success', 'System restored successfully! Please log in again if session expired.');

        } catch (\Exception $e) {
            // Re-enable checks just in case
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('error', 'Restore Failed: ' . $e->getMessage());
        }
    }

}