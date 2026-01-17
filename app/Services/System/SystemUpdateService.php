<?php

namespace App\Services\System;

use Illuminate\Support\Facades\Http;

class SystemUpdateService
{
    /**
     * Check for available updates.
     */
    public function checkForUpdates(bool $isBeta, array $currentVersionConfig): array
    {
        // Beta testers look at 'beta-version.json', others look at 'version.json'
        $url = $isBeta
            ? 'https://raw.githubusercontent.com/aljayvee/pos-system/main/beta-version.json'
            : 'https://raw.githubusercontent.com/aljayvee/pos-system/main/version.json';

        try {
            $response = Http::get($url);
            if ($response->successful()) {
                $latest = $response->json();
                $hasUpdate = (int) $latest['build'] > (int) $currentVersionConfig['build'];

                return [
                    'success' => true,
                    'has_update' => $hasUpdate,
                    'current' => $currentVersionConfig['full'],
                    'latest' => $latest['full'] . ($isBeta ? ' (BETA)' : ''),
                    'type' => $latest['update_type'],
                    'changelog' => $latest['changelog']
                ];
            }
            return ['success' => false, 'error' => 'Failed to fetch update info.'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Offline or unreachable.'];
        }
    }

    /**
     * Run the update sequence (Git pull, Migration, Optimization).
     */
    public function performUpdate(bool $isBeta): array
    {
        set_time_limit(300); // 5 minutes max

        $branch = $isBeta ? 'develop' : 'main';
        $path = base_path();
        $log = [];

        $log[] = "Environment: " . php_uname();
        $log[] = "Root Path: $path";
        $log[] = "Target Branch: $branch";

        try {
            // Helper to run commands and trap output
            $run = function ($cmd) use (&$log, $path) {
                // Determine OS to silence stderr if needed, but we want to see it.
                // Redirect stderr to stdout to capture errors
                $command = "cd \"$path\" && $cmd 2>&1";
                $output = shell_exec($command);
                $log[] = "> $cmd";
                $log[] = trim($output);
                return $output;
            };

            // --- GIT OPERATIONS ---

            // Mark directory as safe (Fixes dubious ownership on Linux/OpenWrt)
            $run("git config --global --add safe.directory \"$path\"");

            // Reset/Stash local changes (User's workflow)
            $run("git stash");

            // PULL changes (User's preferred workflow)
            $run("git pull origin $branch");

            // --- POST-UPDATE TASKS ---

            // Permissions (Only run on Linux to avoid access denied on Windows)
            if (PHP_OS_FAMILY !== 'Windows') {
                $log[] = "Applying Linux Permissions...";
                // Use standard permissions for web server (usually www-data)
                $run("chown -R network:www-data storage bootstrap/cache");
                $run("chmod -R 775 storage bootstrap/cache");
            } else {
                $log[] = "Skipping permissions (Windows detected).";
            }

            // Optimization
            $run("php artisan optimize:clear");
            $run("php artisan migrate --force");

            // Reload Opcache if available
            if (function_exists('opcache_reset')) {
                opcache_reset();
                $log[] = "Opcache reset.";
            }

            return [
                'success' => true,
                'message' => 'Update sequence completed.',
                'output' => implode("\n", $log)
            ];

        } catch (\Exception $e) {
            $log[] = "CRITICAL ERROR: " . $e->getMessage();
            return [
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
                'output' => implode("\n", $log)
            ];
        }
    }
}
