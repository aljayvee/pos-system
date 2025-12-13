<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
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

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $storeId = $this->getActiveStoreId();
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            // SPECIAL CASE: Multi-store toggle is ALWAYS saved to Store 1 (Global)
            if ($key === 'enable_multi_store') {
                Setting::updateOrCreate(
                    ['key' => $key, 'store_id' => 1], // Always ID 1
                    ['value' => $value]
                );
            } 
            else {
                // All other settings (Printer, Header, Loyalty) are saved for the CURRENT Store
                Setting::updateOrCreate(
                    ['key' => $key, 'store_id' => $storeId], 
                    ['value' => $value]
                );
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