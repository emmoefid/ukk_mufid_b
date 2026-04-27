<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KasirController;
use App\Http\Controllers\Api\ManajerController;
use App\Http\Controllers\Api\AdminController;

// Route public (tanpa auth)
Route::post('/login', [AuthController::class, 'login']);

// Route yang butuh auth
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Route Kasir (hanya untuk role kasir)
    Route::middleware(['role:kasir'])->group(function () {
        Route::get('/kasir/menu', [KasirController::class, 'getMenu']);
        Route::get('/kasir/tables', [KasirController::class, 'getAvailableTables']);
        Route::post('/kasir/transaction', [KasirController::class, 'createTransaction']);
        Route::get('/kasir/transactions', [KasirController::class, 'myTransactions']);
        Route::get('/kasir/transaction/{id}', [KasirController::class, 'transactionDetail']);
    });

    // Route Manajer (hanya untuk role manajer)
    Route::middleware(['role:manajer'])->group(function () {
        // Manajemen Menu
        Route::get('/manajer/menu', [ManajerController::class, 'getMenu']);
        Route::post('/manajer/menu', [ManajerController::class, 'addMenu']);
        Route::put('/manajer/menu/{id}', [ManajerController::class, 'updateMenu']);
        Route::delete('/manajer/menu/{id}', [ManajerController::class, 'deleteMenu']);

        // Laporan Transaksi
        Route::get('/manajer/transactions', [ManajerController::class, 'allTransactions']);
        Route::get('/manajer/report/daily', [ManajerController::class, 'dailyReport']);
        Route::get('/manajer/report/monthly', [ManajerController::class, 'monthlyReport']);
        Route::get('/manajer/kasir-list', [ManajerController::class, 'getKasirList']);

        // Log Aktivitas
        Route::get('/manajer/activity-logs', [ManajerController::class, 'activityLogs']);
    });

    // Route Admin (hanya untuk role admin)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/users', [AdminController::class, 'getAllUsers']);
        Route::get('/admin/users/{id}', [AdminController::class, 'getUserDetail']);
        Route::post('/admin/users', [AdminController::class, 'addUser']);
        Route::put('/admin/users/{id}/role', [AdminController::class, 'updateUserRole']);
        Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);
        Route::get('/admin/activity-logs', [AdminController::class, 'activityLogs']);
    });
});
