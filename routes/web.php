<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Cashier\POSController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController; // <--- This was likely missing
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CreditController;

// Public Routes
Route::get('/', function () { return redirect('/login'); });
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ADMIN Routes (Protected)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Management Routes
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);

    //Customer Contoller
    Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);

    // Credit Management
    Route::get('/credits', [CreditController::class, 'index'])->name('credits.index');
    Route::post('/credits/{credit}/pay', [CreditController::class, 'repay'])->name('credits.repay');
    Route::get('/credits/export', [\App\Http\Controllers\Admin\CreditController::class, 'export'])->name('credits.export');

    // User Management
    
    Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
    // Removed 'update' from the exception list
    Route::resource('users', UserController::class)->except(['show', 'edit']);

    // Add this new route:
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');

    // Inventory / Restocking Routes
    Route::resource('purchases', \App\Http\Controllers\Admin\PurchaseController::class)->only(['index', 'create', 'store']);

    // Report Routes
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    // ADD THIS NEW ROUTE:
    Route::get('/reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('reports.export');

    // Inventory & Adjustment Routes
    Route::get('/inventory', [App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/adjust', [App\Http\Controllers\Admin\InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::post('/inventory/adjust', [App\Http\Controllers\Admin\InventoryController::class, 'process'])->name('inventory.process');
    Route::get('/inventory/history', [App\Http\Controllers\Admin\InventoryController::class, 'history'])->name('inventory.history');
    Route::get('/inventory/export', [\App\Http\Controllers\Admin\InventoryController::class, 'export'])->name('inventory.export');

    Route::post('/products/import', [\App\Http\Controllers\Admin\ProductController::class, 'import'])->name('products.import');
    Route::resource('products', ProductController::class);

    // Settings Routes
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::get('/credits/{credit}/history', [CreditController::class, 'history'])->name('credits.history');

    
    // NEW SIDEBAR ROUTE
    Route::get('/credits/payment-logs', [CreditController::class, 'paymentLogs'])->name('credits.logs');

    // Supplier Management
    Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class)->except(['create', 'show', 'edit']);

    // Transaction History & Management
    Route::resource('transactions', \App\Http\Controllers\Admin\TransactionController::class)->only(['index', 'show', 'destroy']);

    //Product
    Route::get('/products/{product}/barcode', [\App\Http\Controllers\Admin\ProductController::class, 'printBarcode'])->name('products.barcode');
});

// CASHIER Routes (Protected)
Route::middleware(['auth', 'role:cashier'])->prefix('cashier')->group(function () {
    Route::get('/pos', [POSController::class, 'index'])->name('cashier.pos');
    Route::post('/transaction', [POSController::class, 'store'])->name('cashier.store');

    // NEW RECEIPT ROUTE
    Route::get('/receipt/{sale}', [POSController::class, 'showReceipt'])->name('cashier.receipt');
});