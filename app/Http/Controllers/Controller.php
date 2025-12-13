<?php

namespace App\Http\Controllers;

abstract class Controller
{
    // Helper to get current Store ID
    protected function getActiveStoreId()
    {
        // 1. Check if Multi-Store is ON (Global Setting)
        $isEnabled = \App\Models\Setting::where('key', 'enable_multi_store')
                        ->where('store_id', 1) // Always check Main Store for this global toggle
                        ->value('value');

        if ($isEnabled !== '1') {
            return 1; // Default to Main Store
        }

        // 2. Return Active Context (or User's assigned store)
        // Fallback to 1 if session/user has no store
        return session('active_store_id', auth()->user()->store_id ?? 1);
    }
}