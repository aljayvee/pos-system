<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'role' => $request->user()->role,
                    'effective_permissions' => $request->user()->effective_permissions,
                    'profile_photo_path' => $request->user()->profile_photo_path ? asset('storage/' . $request->user()->profile_photo_path) : '',
                    'store_id' => $request->user()->store_id,
                ] : null,
            ],
            // Global stats passed down to Sidebar/Header
            'stats' => [
                'outOfStock' => $request->user() ? \App\Models\Inventory::where('store_id', $request->user()->store_id ?? 1)->where('stock', '<=', 0)->whereHas('product')->count() : 0,
                'lowStock' => $request->user() ? \App\Models\Inventory::where('store_id', $request->user()->store_id ?? 1)->where('stock', '>', 0)->whereColumn('stock', '<=', 'reorder_point')->whereHas('product')->count() : 0,
            ],
            // Global settings
            'settings' => [
                'enable_register_logs' => \App\Models\Setting::where('key', 'enable_register_logs')->value('value') ?? 0,
                'enable_bir_compliance' => config('safety_flag_features.bir_tax_compliance') ? true : false,
                'system_mode' => config('app.mode', 'single'),
            ],
            // Flash messages
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
                'warning' => fn() => $request->session()->get('warning'),
                'info' => fn() => $request->session()->get('info'),
            ],
        ]);
    }
}
