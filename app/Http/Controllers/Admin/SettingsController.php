<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt; // Import Crypt
use Illuminate\Support\Facades\Hash; // Import Hash
use Illuminate\Contracts\Encryption\DecryptException; // Import Exception
use App\Models\ActivityLog; // <--- Import this

class SettingsController extends Controller
{
    public function index()
    {
        $storeId = $this->getActiveStoreId();

        // 1. Load Global Settings (Store 1) - Specifically for the Toggle
        $globalSettings = Setting::where('store_id', 1)->where('key', 'enable_multi_store')->pluck('value', 'key');

        // 2. Load Branch Settings (Current Store)
        $branchSettings = Setting::where('store_id', $storeId)->pluck('value', 'key');

        // Merge them so the view sees both
        $settings = $branchSettings->merge($globalSettings);

        // DECRYPT SENSITIVE DATA FOR VIEWING
        // We check if it's encrypted; if not (legacy data), we show it as is.
        foreach (['store_tin', 'business_permit'] as $key) {
            if (isset($settings[$key]) && !empty($settings[$key])) {
                try {
                    $settings[$key] = Crypt::decryptString($settings[$key]);
                } catch (DecryptException $e) {
                    // Value was plain text (not yet encrypted), keep as is
                }
            }
        }

        return view('admin.settings.index', compact('settings'));
    }

    // NEW: Secure Reveal Method
    public function reveal(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'key' => 'required|in:store_tin,business_permit'
        ]);

        // 1. Verify Admin Password
        if (!Hash::check($request->password, auth()->user()->password)) {
            return response()->json(['success' => false, 'message' => 'Incorrect Admin Password'], 403);
        }

        // 2. Fetch & Decrypt
        $storeId = $this->getActiveStoreId();
        $encryptedValue = Setting::where('store_id', $storeId)->where('key', $request->key)->value('value');

        if (!$encryptedValue) {
            return response()->json(['success' => true, 'value' => '']);
        }

        try {
            $decrypted = Crypt::decryptString($encryptedValue);
        } catch (DecryptException $e) {
            $decrypted = $encryptedValue; // Fallback if legacy/plain text
        }

        // Log this security event
        ActivityLog::create([
            'store_id' => $storeId,
            'user_id' => auth()->id(),
            'action' => 'Security Access',
            'description' => 'Viewed sensitive field: ' . $request->key
        ]);

        return response()->json(['success' => true, 'value' => $decrypted]);
    }


    public function update(Request $request)
    {
        $storeId = $this->getActiveStoreId();
        $data = $request->except('_token');

        // --- 1. VALIDATE BIR REQUIREMENTS ---
        // If enabling tax, ensure TIN and Permit exist (either in this request OR already in DB)
        if (isset($data['enable_tax']) && $data['enable_tax'] == '1') {

            $hasTin = !empty($data['store_tin']) ||
                Setting::where('store_id', $storeId)->where('key', 'store_tin')->where('value', '!=', '')->exists();

            $hasPermit = !empty($data['business_permit']) ||
                Setting::where('store_id', $storeId)->where('key', 'business_permit')->where('value', '!=', '')->exists();

            if (!$hasTin || !$hasPermit) {
                $data['enable_tax'] = '0'; // Force OFF
                session()->flash('warning', 'BIR Compliance could not be enabled. TIN and Business Permit are required.');
            }
        }

        // --- 2. SAVE SETTINGS (Existing Logic) ---
        foreach ($data as $key => $value) {

            // Skip empty sensitive fields (don't overwrite existing data with blanks)
            if (in_array($key, ['store_tin', 'business_permit']) && empty($value)) {
                continue;
            }

            // Encrypt if provided
            if (in_array($key, ['store_tin', 'business_permit']) && !empty($value)) {
                try {
                    $value = Crypt::encryptString($value);
                } catch (\Exception $e) {
                    return back()->with('error', 'Encryption failed for ' . $key);
                }
            }

            if ($key === 'enable_multi_store') {
                Setting::updateOrCreate(['key' => $key, 'store_id' => 1], ['value' => $value]);
            } else {
                Setting::updateOrCreate(['key' => $key, 'store_id' => $storeId], ['value' => $value]);
            }
        }

        ActivityLog::create([
            'store_id' => $storeId,
            'user_id' => auth()->id(),
            'action' => 'Settings Update',
            'description' => 'Updated configuration for Branch #' . $storeId
        ]);

        // SYNC: If store_name was updated, sync it to the actual Store model
        if (isset($data['store_name']) && !empty($data['store_name'])) {
            $store = \App\Models\Store::find($storeId);
            if ($store) {
                $store->update(['name' => $data['store_name']]);
            }
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    // Add this new method
    public function verifyDisableBir(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'tin' => 'required',
            'permit' => 'required'
        ]);

        $storeId = $this->getActiveStoreId();
        $user = auth()->user();

        // 1. Check Admin Password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Incorrect Admin Password.']);
        }

        // 2. Fetch Stored Credentials
        $storedTinEnc = Setting::where('store_id', $storeId)->where('key', 'store_tin')->value('value');
        $storedPermitEnc = Setting::where('store_id', $storeId)->where('key', 'business_permit')->value('value');

        try {
            $storedTin = Crypt::decryptString($storedTinEnc);
            $storedPermit = Crypt::decryptString($storedPermitEnc);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Data error. Credentials invalid.']);
        }

        // 3. Compare (Case insensitive)
        if (trim($request->tin) !== $storedTin) {
            return response()->json(['success' => false, 'message' => 'TIN ID does not match our records.']);
        }

        if (trim($request->permit) !== $storedPermit) {
            return response()->json(['success' => false, 'message' => 'Business Permit does not match our records.']);
        }

        // 4. Log Success
        ActivityLog::create([
            'store_id' => $storeId,
            'user_id' => $user->id,
            'action' => 'Compliance Update',
            'description' => 'Disabling BIR Tax Compliance'
        ]);

        return response()->json(['success' => true]);
    }


    public function checkUpdate()
    {
        $current = config('version');
        $storeId = $this->getActiveStoreId();

        // Check if user is a Beta Tester
        $isBeta = Setting::where('store_id', $storeId)->where('key', 'enable_beta')->value('value') == '1';

        // Beta testers look at 'beta-version.json', others look at 'version.json'
        $url = $isBeta
            ? 'https://raw.githubusercontent.com/aljayvee/pos-system/main/beta-version.json'
            : 'https://raw.githubusercontent.com/aljayvee/pos-system/main/version.json';

        try {
            $response = Http::get($url);
            if ($response->successful()) {
                $latest = $response->json();
                $hasUpdate = (int) $latest['build'] > (int) $current['build'];

                return response()->json([
                    'has_update' => $hasUpdate,
                    'current' => $current['full'],
                    'latest' => $latest['full'] . ($isBeta ? ' (BETA)' : ''),
                    'type' => $latest['update_type'],
                    'changelog' => $latest['changelog']
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Offline'], 500);
        }
    }

    public function runUpdate(Request $request)
    {
        set_time_limit(300); // 5 minutes max
        $storeId = $this->getActiveStoreId();

        // 1. Determine Branch
        $isBeta = Setting::where('store_id', $storeId)->where('key', 'enable_beta')->value('value') == '1';
        $branch = $isBeta ? 'develop' : 'main';

        // 2. Determine Path dynamically (Fixes hardcoded /www/pos)
        $path = base_path();

        // 3. Prepare Log Collection
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

            return response()->json([
                'success' => true,
                'message' => 'Update sequence completed.',
                'output' => implode("\n", $log)
            ]);

        } catch (\Exception $e) {
            $log[] = "CRITICAL ERROR: " . $e->getMessage();
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
                'output' => implode("\n", $log)
            ]);
        }
    }

}