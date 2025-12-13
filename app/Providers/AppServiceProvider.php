<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View; // <--- THIS WAS MISSING
use App\Models\Product;
use App\Models\CustomerCredit;
use Carbon\Carbon;

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
        Paginator::useBootstrapFive();

        // NEW: Share Notification Data with Admin Layout
        View::composer('admin.layout', function ($view) {
            // 1. Low Stock Count
            $lowStockCount = Product::whereColumn('stock', '<=', 'reorder_point')->count();
            
            // 2. Out of Stock Count
            $outOfStockCount = Product::where('stock', 0)->count();

            // 3. Overdue Credit Count
            $overdueCount = CustomerCredit::where('is_paid', false)
                                          ->whereDate('due_date', '<', Carbon::now())
                                          ->count();

            $totalAlerts = $lowStockCount + $outOfStockCount + $overdueCount;

            $view->with(compact('lowStockCount', 'outOfStockCount', 'overdueCount', 'totalAlerts'));
        });


        // Share alert counts with all views (Sidebar badges)
    View::composer('*', function ($view) {
        if (auth()->check() && auth()->user()->role === 'admin') {
            $lowStockCount = Product::whereColumn('stock', '<=', 'reorder_point')->count();
            
            $outOfStockCount = Product::where('stock', 0)->count();
            
            $overdueCount = CustomerCredit::where('is_paid', false)
                                          ->whereDate('due_date', '<', now())
                                          ->count();

            $totalAlerts = $lowStockCount + $outOfStockCount + $overdueCount;

            $view->with(compact('lowStockCount', 'outOfStockCount', 'overdueCount', 'totalAlerts'));
        }
    });

    }
}
