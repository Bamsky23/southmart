<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DocController;
use App\Http\Controllers\CategoryController;

// Redirect home to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/quick-login/{role}/{branchId?}', [AuthController::class, 'quickLogin'])->name('quick-login');
Route::any('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Routes (Pusat HQ)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/activity-logs/realtime', [AdminController::class, 'realtimeActivityLogs'])->name('activity-logs.realtime');
    Route::get('/monitoring', [AdminController::class, 'monitoring'])->name('monitoring');
    Route::get('/penjualan-nasional', [AdminController::class, 'nationalSales'])->name('national-sales');
    Route::get('/query-lintas-node', [AdminController::class, 'crossNodeQuery'])->name('cross-node-query');
    
    // Product Master CRUD
    Route::resource('produk', ProductController::class)->except(['show']);

    // Category Master CRUD
    Route::resource('kategori', CategoryController::class)->except(['show']);
    
    // Inventory
    Route::get('/inventaris', [AdminController::class, 'inventory'])->name('inventory');
    Route::post('/inventaris/mutasi', [AdminController::class, 'inventoryMutation'])->name('inventory.mutation');
    
    // Branch Management
    Route::get('/cabang', [AdminController::class, 'branches'])->name('branches');
    
    // Replication & Sync Panel
    Route::get('/replikasi', [AdminController::class, 'replication'])->name('replication');
    Route::post('/sync-all', [AdminController::class, 'syncAllNodes'])->name('sync-all');
    Route::post('/run-replication', [AdminController::class, 'runReplication'])->name('run-replication');
    Route::post('/check-consistency', [AdminController::class, 'checkConsistency'])->name('check-consistency');
    Route::post('/toggle-node/{branchId}', [AdminController::class, 'toggleNode'])->name('toggle-node');

    // Reports
    Route::get('/laporan', [AdminController::class, 'reports'])->name('reports');
    Route::post('/laporan/generate', [AdminController::class, 'generateReport'])->name('reports.generate');
    Route::get('/laporan/download/{id}', [AdminController::class, 'downloadReport'])->name('reports.download');

    // Users & Settings
    Route::get('/pengguna', [AdminController::class, 'users'])->name('users');
    Route::get('/pengaturan', [AdminController::class, 'settings'])->name('settings');
});

// Cashier Routes (POS Branch Nodes)
Route::middleware(['auth', 'role:kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/pos', [CashierController::class, 'pos'])->name('pos');
    Route::post('/checkout', [CashierController::class, 'checkout'])->name('checkout');
    Route::get('/receipt/{transactionCode}', [CashierController::class, 'receipt'])->name('receipt');
    Route::get('/receipt/{transactionCode}/download', [CashierController::class, 'downloadReceipt'])->name('receipt.download');
    Route::get('/history', [CashierController::class, 'history'])->name('history');
    Route::post('/sync-local', [CashierController::class, 'syncLocalTransactions'])->name('sync-local');
    Route::post('/void-latest', [CashierController::class, 'voidLatestTransaction'])->name('void-latest');
});

// Documentation Module (Accessible to all authenticated users)
Route::middleware(['auth'])->prefix('dokumen')->name('doc.')->group(function () {
    Route::get('/', [DocController::class, 'index'])->name('index');
    Route::get('/{topic}', [DocController::class, 'show'])->name('show');
});
