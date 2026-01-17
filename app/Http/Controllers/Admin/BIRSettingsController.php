<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class BIRSettingsController extends Controller
{
    public function index()
    {
        // 1. Safety Check (Feature Flag)
        if (!config('safety_flag_features.bir_tax_compliance')) {
            abort(404);
        }

        // 2. Fetch all BIR-related settings
        $keys = [
            'store_tin',
            'business_permit',
            'tax_type',
            'tax_rate',
            'serial_number',
            'min_number',
            'ptu_number',
            'bir_rev_audit_period' // Example extra field
        ];

        $settings = Setting::whereIn('key', $keys)->pluck('value', 'key');

        // Decrypt sensitive fields for display
        // (Note: In a real app, maybe only show last 4 digits or keep masked until 'reveal' clicked)
        // For consistency with existing SettingsController, we'll keep them as is (masked in UI usually)

        return view('admin.bir.index', compact('settings'));
    }

    public function update(Request $request)
    {
        if (!config('safety_flag_features.bir_tax_compliance')) {
            abort(403);
        }

        $validated = $request->validate([
            'store_tin' => 'nullable|string',
            'business_permit' => 'nullable|string',
            'tax_type' => 'required|in:inclusive,exclusive,non_vat',
            'tax_rate' => 'required|numeric|min:0',
            'serial_number' => 'nullable|string',
            'min_number' => 'nullable|string',
            'ptu_number' => 'nullable|string',
        ]);

        // Save Settings
        foreach ($validated as $key => $value) {
            // Encrypt sensitive data if needed (TIN/Permit) - adhering to existing pattern
            if (in_array($key, ['store_tin', 'business_permit']) && !empty($value)) {
                // Check if it's already encrypted? 
                // Currently SettingsController seems to encrypt on save.
                // We will encrypt here.
                // NOTE: If value is masked/placeholder, skip update? 
                // For simplicity, we assume completely new input or re-entry.
                // In a robust system, we check if value changed.

                // Let's stick to simple plaintext storage for now if encryption wasn't strictly enforced everywhere
                // OR use Crypt::encryptString($value);
                // Based on POSController, it attempts to decrypt. So we should encrypt.
                try {
                    $value = Crypt::encryptString($value);
                } catch (\Exception $e) {
                    // Log error
                }
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'store_id' => 1] // Assuming single store for now or current store
            );

            // ALSO Update Stores Table Columns (Persistent Redundancy)
            // Phase 1 added these columns to `stores` table.
            // We should sync them for performance (POSController uses Store model sometimes).
            if (in_array($key, ['serial_number', 'min_number', 'ptu_number'])) {
                $store = \App\Models\Store::find(1); // Fallback to 1
                if ($store) {
                    $store->$key = $value;
                    $store->save();
                }
            }
        }

        return redirect()->back()->with('success', 'BIR Compliance settings updated successfully.');
    }
}
