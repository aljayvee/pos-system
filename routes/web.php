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
use App\Http\Controllers\Admin\ReturnController;
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
// ADMIN Routes (Protected)
// Allow all "Back Office" roles to enter the admin area
Route::middleware(['auth', 'role:admin,manager,supervisor,stock_clerk,auditor'])->prefix('admin')->group(function () {
    
    // Dashboard (Accessible by all back-office roles)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // 1. INVENTORY MANAGEMENT
    // 1. INVENTORY MANAGEMENT
    Route::middleware(['role:inventory.view'])->group(function () {
        // Read-Only Access
        Route::resource('categories', CategoryController::class)->only(['index', 'show']);
        Route::resource('products', ProductController::class)->only(['index', 'show']);
        Route::get('/products/{product}/barcode', [ProductController::class, 'printBarcode'])->name('products.barcode'); // Print is read-only?
        Route::resource('purchases', \App\Http\Controllers\Admin\PurchaseController::class)->only(['index', 'show']);
        
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/history', [InventoryController::class, 'history'])->name('inventory.history');
        Route::get('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');

        // Write Access Restricted to 'inventory.edit'
        Route::middleware(['role:inventory.edit'])->group(function() {
             Route::resource('categories', CategoryController::class)->except(['index', 'show']);
             Route::resource('products', ProductController::class)->except(['index', 'show']);
             
             Route::post('/products/check-duplicate', [ProductController::class, 'checkDuplicate'])->name('products.check_duplicate');
             Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
             Route::post('/products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
             Route::delete('/products/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('products.force_delete');

             Route::resource('purchases', \App\Http\Controllers\Admin\PurchaseController::class)->only(['create', 'store', 'destroy']);
        });

        // Restrict Adjustment VIEW and ACTION to 'inventory.adjust' permission
        Route::middleware(['role:inventory.adjust'])->group(function() {
            Route::get('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
            Route::post('/inventory/adjust', [InventoryController::class, 'storeAdjustment'])->name('inventory.storeAdjustment');
        });
    });

    // 2. FINANCE & CUSTOMERS (Using sales.view as base)
    // Customers/Suppliers/Credits usually linked to Sales
    Route::middleware(['role:sales.view,reports.view'])->group(function () {
        // READ-ONLY Access (Auditors, Supervisors, etc.)
        Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class)->only(['index', 'show']);
        Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class)->only(['index', 'show']);
        
        Route::get('/credits', [CreditController::class, 'index'])->name('credits.index');
        Route::get('/credits/export', [CreditController::class, 'export'])->name('credits.export');
        Route::get('/credits/{credit}/history', [CreditController::class, 'history'])->name('credits.history');
        Route::get('/credits/payment-logs', [CreditController::class, 'paymentLogs'])->name('credits.logs');

        // WRITE Access (Admin & Manager Only)
        // Adjust/Pay/Create/Edit/Delete
        Route::middleware(['role:admin,manager'])->group(function() {
            Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class)->except(['index', 'show']);
            Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class)->except(['index', 'show']);
            Route::post('/credits/{credit}/pay', [CreditController::class, 'storePayment'])->name('credits.pay'); 
        });
    });

    // 3. USER MANAGEMENT
    // 3. USER MANAGEMENT
    Route::middleware(['role:user.manage'])->group(function () {
        // Read-Only for anyone with user.manage (including Auditor)
        Route::resource('users', UserController::class)->only(['index']);

        // Write/Execute Actions (Admin & Manager Only)
        Route::middleware(['role:admin,manager'])->group(function() {
            Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
            Route::post('/admin/verify-override', [UserController::class, 'verifyOverride'])->name('admin.verify_override');
            
            // ASYNC ROLE APPROVAL (New)
            Route::post('/approval/send', [\App\Http\Controllers\Admin\ApprovalController::class, 'sendRequest'])->name('approval.send');
            Route::get('/approval/{id}/status', [\App\Http\Controllers\Admin\ApprovalController::class, 'checkStatus'])->name('approval.status');
            Route::get('/approval/pending', [\App\Http\Controllers\Admin\ApprovalController::class, 'getPending'])->name('approval.pending');
            Route::post('/approval/{id}/decide', [\App\Http\Controllers\Admin\ApprovalController::class, 'decideRequest'])->name('approval.decide');

            // Create, Edit, Update, Destroy
            Route::get('/users/archived', [UserController::class, 'archived'])->name('users.archived');
            Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
            Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.force_delete');
            
            Route::resource('users', UserController::class)->except(['index', 'show']);
        });
    });

    // 4. REPORTS & ANALYTICS
    Route::middleware(['role:reports.view'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/forecast', [ReportController::class, 'forecast'])->name('reports.forecast');
        Route::get('/reports/inventory', [ReportController::class, 'inventoryReport'])->name('reports.inventory');
        Route::get('/reports/credits', [ReportController::class, 'creditReport'])->name('reports.credits');
    });

    // 5. TRANSACTIONS & RETURNS (sales.view)
    Route::middleware(['role:sales.view'])->group(function () {
        Route::resource('transactions', TransactionController::class)->only(['index', 'show', 'destroy']);
        Route::get('/transactions/{sale}/print', [TransactionController::class, 'printReceipt'])->name('transactions.print');
        
        Route::get('/transactions/{sale}/return', [ReturnController::class, 'create'])->name('admin.transactions.return');
        Route::post('/transactions/{sale}/return', [ReturnController::class, 'store'])->name('admin.transactions.process_return');
    });

    // 6. LOGS (logs.view)
    Route::middleware(['role:logs.view'])->group(function () {
        Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
    });

    // 7. SETTINGS & SYSTEM (settings.manage)
    Route::middleware(['role:settings.manage'])->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/reveal', [SettingsController::class, 'reveal'])->name('settings.reveal');
        Route::post('/settings/verify-disable-bir', [SettingsController::class, 'verifyDisableBir'])->name('settings.verify_disable_bir');
        
        Route::get('/settings/backup', [BackupController::class, 'download'])->name('settings.backup');
        Route::post('/settings/restore', [BackupController::class, 'restore'])->name('settings.restore');
        Route::get('/settings/update-check', [SettingsController::class, 'checkUpdate'])->name('settings.check_update');
        Route::post('/settings/update-process', [SettingsController::class, 'runUpdate'])->name('settings.run_update');
        
        Route::resource('stores', \App\Http\Controllers\Admin\StoreController::class);
        Route::get('/stores/switch/{id}', [\App\Http\Controllers\Admin\StoreController::class, 'switch'])->name('stores.switch');
    });

});

// CASHIER Routes (Protected)
Route::middleware(['auth', 'role:cashier,admin'])->prefix('cashier')->group(function () {
    Route::get('/pos', [POSController::class, 'index'])->name('cashier.pos');
    Route::post('/transaction', [POSController::class, 'store'])->name('cashier.store');
    Route::get('/receipt/{sale}', [POSController::class, 'showReceipt'])->name('cashier.receipt');
   

    // NEW: Add this missing route
    Route::post('/credit-payment', [POSController::class, 'payCredit'])->name('cashier.credit.pay');

    // PayMongo Routes
    Route::post('/payment/create', [\App\Http\Controllers\Cashier\PaymentController::class, 'createSource'])->name('payment.create');
    Route::get('/payment/check/{id}', [\App\Http\Controllers\Cashier\PaymentController::class, 'checkStatus'])->name('payment.check');

    // NEW: Return Routes
    Route::get('/return/search', [POSController::class, 'searchSale'])->name('cashier.return.search');
    Route::post('/return/process', [POSController::class, 'processReturn'])->name('cashier.return.process');
    // NEW: X and Z Reading Route
    Route::get('/reading/{type}', [POSController::class, 'showReading'])->name('cashier.reading');

    // ... inside 'cashier' prefix group ...
    Route::get('/debtors', [POSController::class, 'getDebtors'])->name('cashier.debtors');

    // In routes/web.php (inside cashier group)
    Route::get('/inventory/sync', [POSController::class, 'getStockUpdates'])->name('cashier.inventory.sync');
    Route::post('/verify-admin', [POSController::class, 'verifyAdmin'])->name('cashier.verify_admin');


});

// Authenticated User Routes (Profile)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');


    // Device 2
    Route::get('/auth/wait', [AuthController::class, 'showConsentWait'])->name('auth.consent.wait');
    Route::get('/auth/check-status', [AuthController::class, 'checkConsentStatus'])->name('auth.consent.check');

     Route::get('/auth/check-requests', [AuthController::class, 'checkLoginRequests'])->name('auth.check_requests');
    Route::post('/auth/resolve-request', [AuthController::class, 'resolveLoginRequest'])->name('auth.resolve_request');
    
    // NEW: Force Login Routes
    Route::post('/auth/force-email', [AuthController::class, 'sendForceLoginEmail'])->name('auth.force.email');
    // Note: The verify route does NOT need auth middleware strictly if clicking from email on a fresh phone, 
    // but usually better to have signed middleware.
});

// MOVE THIS OUTSIDE THE AUTH GROUP (Publicly accessible with signature)
Route::get('/auth/force-verify/{id}', [AuthController::class, 'verifyForceLogin'])
    ->name('auth.force_login_verify')
    ->middleware('signed');


// ...