<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt; // Import Crypt
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

    public function update(Request $request)
    {
        $storeId = $this->getActiveStoreId();
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            
            // ENCRYPT SENSITIVE DATA BEFORE SAVING
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

        // Log to the specific store's log
        \App\Models\ActivityLog::create([
            'store_id' => $storeId,
            'user_id' => auth()->id(),
            'action' => 'Settings Update',
            'description' => 'Updated configuration for Branch #' . $storeId
        ]);

        return back()->with('success', 'Settings updated for this branch.');
    }
}