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

    // User Management
    Route::resource('users', UserController::class)->except(['show', 'edit', 'update']);
    Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
});

// CASHIER Routes (Protected)
Route::middleware(['auth', 'role:cashier'])->prefix('cashier')->group(function () {
    Route::get('/pos', [POSController::class, 'index'])->name('cashier.pos');
    Route::post('/transaction', [POSController::class, 'store'])->name('cashier.store');
});