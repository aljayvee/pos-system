<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Cashier\POSController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CreditController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\ProfileController;

// Public Routes
Route::get('/', function () { return redirect('/login'); });
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ADMIN Routes (Protected)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Core Management
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);
    // Removed 'show' from except array
    Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class)->except(['create', 'edit']);
    
    // User Management (With Toggle)
    Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
    Route::resource('users', UserController::class)->except(['show']);

    // Inventory & Adjustment Routes
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::post('/inventory/adjust', [InventoryController::class, 'storeAdjustment'])->name('inventory.storeAdjustment'); // Keep only this one
    Route::get('/inventory/history', [InventoryController::class, 'history'])->name('inventory.history');
    Route::get('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');

    // Product Features
    Route::get('/products/{product}/barcode', [ProductController::class, 'printBarcode'])->name('products.barcode');
    Route::post('/products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
    Route::delete('/products/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('products.force_delete');

    // Purchases (Stock In)
    Route::resource('purchases', \App\Http\Controllers\Admin\PurchaseController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    // Sales & Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::resource('transactions', TransactionController::class)->only(['index', 'show', 'destroy']);
    Route::get('/transactions/{sale}/print', [TransactionController::class, 'printReceipt'])->name('transactions.print');

    // Credit Management
    Route::get('/credits', [CreditController::class, 'index'])->name('credits.index');
    Route::post('/credits/{credit}/pay', [CreditController::class, 'storePayment'])->name('credits.pay'); // Keep only this one
    Route::get('/credits/export', [CreditController::class, 'export'])->name('credits.export');
    Route::get('/credits/{credit}/history', [CreditController::class, 'history'])->name('credits.history');
    Route::get('/credits/payment-logs', [CreditController::class, 'paymentLogs'])->name('credits.logs');

    // Settings & System
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    
    // Backup & Restore
    Route::get('/settings/backup', [BackupController::class, 'download'])->name('settings.backup');
    Route::post('/settings/restore', [BackupController::class, 'restore'])->name('settings.restore');

    // Logs
    Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');

    
    
});

// CASHIER Routes (Protected)
Route::middleware(['auth', 'role:cashier'])->prefix('cashier')->group(function () {
    Route::get('/pos', [POSController::class, 'index'])->name('cashier.pos');
    Route::post('/transaction', [POSController::class, 'store'])->name('cashier.store');
    Route::get('/receipt/{sale}', [POSController::class, 'showReceipt'])->name('cashier.receipt');
    //Route::post('/credit-payment', [POSController::class, 'payCredit'])->name('cashier.credit.pay');

    // NEW: Add this missing route
    Route::post('/credit-payment', [POSController::class, 'payCredit'])->name('cashier.credit.pay');

    // PayMongo Routes
    Route::post('/payment/create', [\App\Http\Controllers\Cashier\PaymentController::class, 'createSource'])->name('payment.create');
    Route::get('/payment/check/{id}', [\App\Http\Controllers\Cashier\PaymentController::class, 'checkStatus'])->name('payment.check');

});

// Authenticated User Routes (Profile)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});