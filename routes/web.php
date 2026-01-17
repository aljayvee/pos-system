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
use App\Http\Controllers\Admin\StorePreferencesController;
// ... (imports)


use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\ProfileController;

// Public Routes
Route::get('/', function () {
    return redirect('/login');
});
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Forgot Password (OTP)
// Password Reset Wizard
Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/search', [\App\Http\Controllers\Auth\PasswordResetController::class, 'search'])->name('password.search');
Route::post('/password/send-otp', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendOtp'])->name('password.sendOtp');
Route::post('/password/verify-otp', [\App\Http\Controllers\Auth\PasswordResetController::class, 'verifyOtp'])->name('password.verifyOtp');
Route::post('/password/reset', [\App\Http\Controllers\Auth\PasswordResetController::class, 'resetWizard'])->name('password.wizard.reset');

// ADMIN Routes (Protected)
// ADMIN Routes (Protected)
// Allow all "Back Office" roles to enter the admin area
// Setup Routes (Public middleware but guarded)
Route::group(['middleware' => ['web']], function () {
    Route::get('/setup', [\App\Http\Controllers\Admin\SetupController::class, 'index'])->name('admin.setup.index');
    Route::post('/setup/step1', [\App\Http\Controllers\Admin\SetupController::class, 'storeStep1'])->name('admin.setup.step1');
    Route::post('/setup/send-otp', [\App\Http\Controllers\Admin\SetupController::class, 'sendOtp'])->name('admin.setup.sendOtp');
    Route::post('/setup/verify', [\App\Http\Controllers\Admin\SetupController::class, 'verifyAndCreate'])->name('admin.setup.verify');

    // New User Onboarding (Authenticated but unverified)
    Route::middleware(['auth'])->group(function () {
        Route::get('/onboarding', [\App\Http\Controllers\Admin\OnboardingController::class, 'index'])->name('onboarding.index');
        Route::post('/onboarding/send-otp', [\App\Http\Controllers\Admin\OnboardingController::class, 'sendOtp'])->name('onboarding.sendOtp');
        Route::post('/onboarding/verify', [\App\Http\Controllers\Admin\OnboardingController::class, 'verify'])->name('onboarding.verify');
        Route::get('/welcome', [\App\Http\Controllers\Admin\OnboardingController::class, 'welcome'])->name('onboarding.welcome');
    });
});

// ADMIN Routes (Protected)
Route::middleware(['auth', 'role:admin,manager,supervisor,stock_clerk,auditor', 'mpin.verify'])->prefix('admin')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // 1. INVENTORY MANAGEMENT
    // 1. INVENTORY MANAGEMENT
    Route::middleware(['role:inventory.view'])->group(function () {

        // Write Access Restricted to 'inventory.edit'
        Route::middleware(['role:inventory.edit'])->group(function () {
            Route::resource('categories', CategoryController::class)->except(['index', 'show']);
            Route::get('/products/batch-create', [App\Http\Controllers\Admin\ProductController::class, 'batchCreate'])->name('products.batch_create');
            Route::post('/products/batch-store', [App\Http\Controllers\Admin\ProductController::class, 'batchStore'])->name('products.batch_store');
            Route::post('/products/import', [App\Http\Controllers\Admin\ProductController::class, 'import'])->name('products.import');
            Route::post('/products/check-duplicate', [App\Http\Controllers\Admin\ProductController::class, 'checkDuplicate'])->name('products.check_duplicate');
            Route::get('/products/{product}/barcode', [App\Http\Controllers\Admin\ProductController::class, 'printBarcode'])->name('products.barcode');
            Route::post('/products/{product}/restore', [App\Http\Controllers\Admin\ProductController::class, 'restore'])->name('products.restore');
            Route::delete('/products/{product}/force-delete', [App\Http\Controllers\Admin\ProductController::class, 'forceDelete'])->name('products.force_delete');
            Route::resource('products', ProductController::class)->except(['index', 'show']);

            Route::resource('purchases', \App\Http\Controllers\Admin\PurchaseController::class)->only(['create', 'store', 'destroy']);
        });

        // Read-Only Access
        Route::resource('categories', CategoryController::class)->only(['index', 'show']);
        Route::get('/categories/{category}/products', [CategoryController::class, 'getProducts'])->name('categories.products');
        Route::resource('products', ProductController::class)->only(['index', 'show']);
        Route::get('/products/{product}/barcode', [ProductController::class, 'printBarcode'])->name('products.barcode'); // Print is read-only?
        Route::resource('purchases', \App\Http\Controllers\Admin\PurchaseController::class)->only(['index', 'show']);

        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/history', [InventoryController::class, 'history'])->name('inventory.history');
        Route::get('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');

        // Restrict Adjustment VIEW and ACTION to 'inventory.adjust' permission
        Route::middleware(['role:inventory.adjust'])->group(function () {
            Route::get('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
            Route::post('/inventory/adjust', [InventoryController::class, 'storeAdjustment'])->name('inventory.storeAdjustment');

            // Stock Transfers
            Route::resource('transfers', \App\Http\Controllers\Admin\TransferController::class)->only(['index', 'create', 'store']);
        });
    });

    // 2. FINANCE & CUSTOMERS (Using sales.view as base)
    // Customers/Suppliers/Credits usually linked to Sales
    Route::middleware(['role:sales.view,reports.view'])->group(function () {

        // WRITE Access (Admin & Manager Only)
        // Adjust/Pay/Create/Edit/Delete
        Route::middleware(['role:admin,manager'])->group(function () {
            Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class)->except(['index', 'show']);
            Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class)->except(['index', 'show']);
            Route::get('/credits/{credit}/pay', [CreditController::class, 'showPaymentForm'])->name('credits.pay_form');
            Route::post('/credits/{credit}/pay', [CreditController::class, 'storePayment'])->name('credits.pay');
        });

        // READ-ONLY Access (Auditors, Supervisors, etc.)
        Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class)->only(['index', 'show']);
        Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class)->only(['index', 'show']);

        Route::get('/credits', [CreditController::class, 'index'])->name('credits.index');
        Route::get('/credits/export', [CreditController::class, 'export'])->name('credits.export');
        Route::get('/credits/{credit}/history', [CreditController::class, 'history'])->name('credits.history');
        Route::get('/credits/payment-logs', [CreditController::class, 'paymentLogs'])->name('credits.logs');
    });

    // 3. USER MANAGEMENT
    // 3. USER MANAGEMENT
    Route::middleware(['role:user.manage'])->group(function () {
        // Read-Only for anyone with user.manage (including Auditor)
        Route::resource('users', UserController::class)->only(['index']);

        // Write/Execute Actions (Admin & Manager Only)
        Route::middleware(['role:admin,manager'])->group(function () {
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

    // CASH CONTROL (Admin/Manager)
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::get('/adjustments', [\App\Http\Controllers\Admin\CashRegisterController::class, 'index'])->name('admin.adjustments');
        Route::post('/register/approve/{id}', [\App\Http\Controllers\Cashier\CashRegisterController::class, 'processAdjustment'])->name('admin.register.approve');
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
        Route::get('/store-preferences', [StorePreferencesController::class, 'index'])->name('settings.index');
        Route::post('/store-preferences', [StorePreferencesController::class, 'update'])->name('settings.update');
        Route::post('/store-preferences/reveal', [StorePreferencesController::class, 'reveal'])->name('settings.reveal');
        Route::post('/store-preferences/verify-disable-bir', [StorePreferencesController::class, 'verifyDisableBir'])->name('settings.verify_disable_bir');

        Route::get('/store-preferences/backup', [BackupController::class, 'download'])->name('settings.backup');
        Route::post('/store-preferences/restore', [BackupController::class, 'restore'])->name('settings.restore');
        Route::get('/store-preferences/update-check', [StorePreferencesController::class, 'checkUpdate'])->name('settings.check_update');
        Route::post('/store-preferences/update-process', [StorePreferencesController::class, 'runUpdate'])->name('settings.run_update');

        Route::resource('stores', \App\Http\Controllers\Admin\StoreController::class);
        Route::get('/stores/switch/{id}', [\App\Http\Controllers\Admin\StoreController::class, 'switch'])->name('stores.switch');
    });

    // 8. BIR COMPLIANCE (Feature Flagged)
    Route::middleware(['role:settings.manage'])->group(function () {
        Route::get('/bir', [\App\Http\Controllers\Admin\BIRSettingsController::class, 'index'])->name('admin.bir.index');
        Route::post('/bir', [\App\Http\Controllers\Admin\BIRSettingsController::class, 'update'])->name('admin.bir.update');
    });

});

// CASH REGISTER ROUTES
Route::middleware(['auth'])->group(function () {
    Route::get('/cashier/register/status', [\App\Http\Controllers\Cashier\CashRegisterController::class, 'status']);
    Route::post('/cashier/register/open', [\App\Http\Controllers\Cashier\CashRegisterController::class, 'open']);
    Route::post('/cashier/register/close', [\App\Http\Controllers\Cashier\CashRegisterController::class, 'close']);

    // Manager/Admin Adjustments
    Route::post('/admin/register/adjust', [\App\Http\Controllers\Cashier\CashRegisterController::class, 'requestAdjustment']);
    Route::post('/admin/register/approve/{id}', [\App\Http\Controllers\Cashier\CashRegisterController::class, 'processAdjustment']);
});

// CASHIER Routes (Protected)
Route::middleware(['auth', 'role:pos.access', 'mpin.verify'])->prefix('cashier')->group(function () {
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
    Route::post('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.update.info');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.update.photo');
    Route::post('/profile/security', [ProfileController::class, 'updateSecurity'])->name('profile.update.security');

    // Email Verify
    Route::post('/profile/verify-email/send', [ProfileController::class, 'initiateEmailVerification'])->name('profile.verify_email.send');
    Route::post('/profile/verify-email/check', [ProfileController::class, 'checkEmailVerification'])->name('profile.verify_email.check');

    // Secure Email Change
    Route::post('/profile/email/initiate', [ProfileController::class, 'initiateEmailChange'])->name('profile.email.initiate');
    Route::post('/profile/email/verify-current', [ProfileController::class, 'verifyCurrentEmailOtp'])->name('profile.email.verify_current');
    Route::post('/profile/email/request-new', [ProfileController::class, 'requestNewEmailOtp'])->name('profile.email.request_new');
    Route::post('/profile/email/confirm-update', [ProfileController::class, 'confirmNewEmail'])->name('profile.email.confirm_update');

    // Secure Password Change (OTP-based)
    Route::post('/profile/password/otp', [ProfileController::class, 'requestPasswordOtp'])->name('profile.password.otp');
    Route::post('/profile/password/verify', [ProfileController::class, 'verifyPasswordOtp'])->name('profile.password.verify');
    Route::post('/profile/password/update', [ProfileController::class, 'updatePasswordViaOtp'])->name('profile.password.update_secure');

    // Secure MPIN Reset (OTP-based)
    Route::post('/profile/mpin/otp', [ProfileController::class, 'requestMpinOtp'])->name('profile.mpin.otp');
    Route::post('/profile/mpin/verify', [ProfileController::class, 'verifyMpinOtp'])->name('profile.mpin.verify');
    Route::post('/profile/mpin/update', [ProfileController::class, 'updateMpinViaOtp'])->name('profile.mpin.update_secure');


    // Device 2
    Route::get('/auth/wait', [AuthController::class, 'showConsentWait'])->name('auth.consent.wait');
    Route::get('/auth/check-status', [AuthController::class, 'checkConsentStatus'])->name('auth.consent.check');

    Route::get('/auth/check-requests', [AuthController::class, 'checkLoginRequests'])->name('auth.check_requests');
    Route::post('/auth/resolve-request', [AuthController::class, 'resolveLoginRequest'])->name('auth.resolve_request');

    // NEW: Force Login Routes
    Route::post('/auth/force-email', [AuthController::class, 'sendForceLoginEmail'])->name('auth.force.email');
    // Note: The verify route does NOT need auth middleware strictly if clicking from email on a fresh phone, 
    // but usually better to have signed middleware.
    // MPIN Routes
    Route::get('/auth/mpin', [App\Http\Controllers\Auth\MpinController::class, 'showMpinForm'])->name('auth.mpin.login');
    Route::post('/auth/mpin', [App\Http\Controllers\Auth\MpinController::class, 'verify'])->name('auth.mpin.verify');

    Route::get('/auth/mpin/setup', [App\Http\Controllers\Auth\MpinController::class, 'showSetupForm'])->name('auth.mpin.setup');
    Route::post('/auth/mpin/setup', [App\Http\Controllers\Auth\MpinController::class, 'store'])->name('auth.mpin.store');

    Route::get('/auth/mpin/forgot', [App\Http\Controllers\Auth\MpinController::class, 'showForgotForm'])->name('auth.mpin.forgot');

    // Step 1: Verify Credentials
    Route::post('/auth/mpin/reset/verify', [App\Http\Controllers\Auth\MpinController::class, 'verifyResetCredentials'])->name('auth.mpin.reset.verify');

    // Step 2: Set New MPIN
    Route::get('/auth/mpin/reset/new', [App\Http\Controllers\Auth\MpinController::class, 'showResetMpinForm'])->name('auth.mpin.reset.form');
    Route::post('/auth/mpin/reset/new', [App\Http\Controllers\Auth\MpinController::class, 'resetMpin'])->name('auth.mpin.reset.perform');

    // NEW: OTP Reset for MPIN
    Route::post('/auth/mpin/reset/send-otp', [App\Http\Controllers\Auth\MpinController::class, 'sendResetOtp'])->name('auth.mpin.reset.send.otp');
});

// MOVE THIS OUTSIDE THE AUTH GROUP (Publicly accessible with signature)
Route::get('/auth/force-verify/{id}', [AuthController::class, 'verifyForceLogin'])
    ->name('auth.force_login_verify')
    ->middleware('signed');


// ... (End of existing file)

// WebAuthn Routes
Route::middleware(['auth', \App\Http\Middleware\WebAuthnDynamicRpId::class])->group(function () {
    Route::post('/webauthn/register/options', [\App\Http\Controllers\WebAuthnController::class, 'options'])
        ->name('webauthn.register.options');
    Route::post('/webauthn/register', [\App\Http\Controllers\WebAuthnController::class, 'register'])
        ->name('webauthn.register');
});

// WebAuthn Login Routes (Unauthenticated)
Route::middleware([\App\Http\Middleware\WebAuthnDynamicRpId::class])->group(function () {
    Route::post('/webauthn/login/options', [\App\Http\Controllers\WebAuthnController::class, 'loginOptions'])
        ->name('webauthn.login.options');
    Route::post('/webauthn/login', [\App\Http\Controllers\WebAuthnController::class, 'login'])
        ->name('webauthn.login');
});