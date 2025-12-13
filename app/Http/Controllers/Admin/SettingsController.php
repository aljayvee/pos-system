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
        // Fetch all settings as a simple key-value pair array
        $settings = Setting::pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Save all input fields as settings
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // LOG ACTION
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Settings Update',
            'description' => 'Updated system configurations (Store Name, Policy, etc.)'
        ]);

        return back()->with('success', 'Settings updated successfully!');
    }
}