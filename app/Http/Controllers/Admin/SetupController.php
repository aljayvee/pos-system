<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetupController extends Controller
{
    public function index()
    {
        return view('admin.setup.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'system_mode' => 'required|in:single,multi',
            'store_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            // Address Fields
            'region' => 'nullable|string',
            'city' => 'nullable|string',
            'barangay' => 'nullable|string',
            'street' => 'nullable|string',
            'country' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // 1. Update Settings
            \App\Models\Setting::updateOrCreate(['key' => 'system_mode'], ['value' => $request->system_mode]);
            \App\Models\Setting::updateOrCreate(['key' => 'store_name'], ['value' => $request->store_name]);

            // 2. Update Master Store (ID 1)
            $store = \App\Models\Store::find(1);
            if (!$store) {
                // Should exist from seed, but create if missing
                $store = new \App\Models\Store();
                $store->id = 1;
            }

            $store->name = $request->store_name;
            $store->owner_name = $request->owner_name;
            $store->contact_number = $request->contact_number;
            $store->country = $request->country ?? 'Philippines';
            $store->region = $request->region;
            $store->city = $request->city;
            $store->barangay = $request->barangay;
            $store->street = $request->street;
            $store->save();

            // 3. Mark Setup as Complete
            \App\Models\Setting::updateOrCreate(['key' => 'setup_complete'], ['value' => '1']);

            DB::commit();

            return redirect()->route('admin.dashboard')->with('success', 'System Setup Completed Successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Setup failed: ' . $e->getMessage())->withInput();
        }
    }
}
