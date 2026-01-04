<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View; // <--- THIS WAS MISSING
use App\Models\Product;
use App\Models\CustomerCredit;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event; // Import Event Facade
use App\Events\SaleCreated; // Import Event
use App\Listeners\LogSaleToJournal; // Import Listener

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // DEFINE GATES FOR PERMISSIONS
        // This bridges Laravel's @can() directive to our Custom RBAC
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasPermission')) {
                if ($user->hasPermission($ability)) {
                    return true;
                }
            }
        });

        Paginator::useBootstrapFive();

        // REGISTER BIR EVENT LISTENER
        Event::listen(SaleCreated::class, LogSaleToJournal::class);

        // NEW: Share Notification Data with Admin Layout
        View::composer('admin.layout', function ($view) {
            // Get Active Store ID (Safe fallback)
            // Get Active Store ID (Safe fallback)
            $storeId = 1;
            if (\App\Models\Setting::where('key', 'enable_multi_store')->value('value') == '1') {
                $user = auth()->user();
                if ($user && $user->role !== 'admin') {
                    $storeId = $user->store_id ?? 1;
                } else {
                    $storeId = session('active_store_id', $user->store_id ?? 1);
                }
            }

            // 1. Low Stock Count (Exclusive of Out of Stock)
            // Query Inventory directly for accurate store-specific stock
            $lowStockCount = \App\Models\Inventory::where('store_id', $storeId)
                ->whereColumn('stock', '<=', 'reorder_point')
                ->where('stock', '>', 0)
                ->count();

            // 2. Out of Stock Count
            $outOfStockCount = \App\Models\Inventory::where('store_id', $storeId)
                ->where('stock', 0)
                ->count();

            // 3. Overdue Credit Count
            $overdueCount = CustomerCredit::where('is_paid', false)
                ->whereDate('due_date', '<', Carbon::now())
                ->count();

            // 4. NEW: Expiring Soon (Next 7 Days + Already Expired)
            $expiringCount = Product::whereNotNull('expiration_date')
                ->where('expiration_date', '<=', now()->addDays(7))
                ->count();

            $totalAlerts = $lowStockCount + $outOfStockCount + $overdueCount + $expiringCount;

            $view->with(compact('lowStockCount', 'outOfStockCount', 'overdueCount', 'expiringCount', 'totalAlerts'));
        });


        // Share alert counts with all views (Sidebar badges)
        // REMOVED Redundant View::composer('*') to prevent N+1 Query Performance Issues
        // The 'admin.layout' composer above already handles this for the main UI.

        // FORCE HTTPS IN PRODUCTION
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

    }
}
