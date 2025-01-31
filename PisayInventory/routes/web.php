<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Items Management
    Route::resource('items', ItemController::class);

    // Inventory Management
    Route::resource('inventory', InventoryController::class);

    // Suppliers Management
    Route::resource('suppliers', SupplierController::class);

    // Classifications Management
    Route::resource('classifications', ClassificationController::class);

    // Units Management
    Route::controller(UnitController::class)->group(function () {
        Route::get('/units', 'index')->name('units.index');
        Route::post('/units', 'store')->name('units.store');
        Route::put('/units/{unit}', 'update')->name('units.update');
        Route::delete('/units/{unit}', 'destroy')->name('units.destroy');
    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/inventory', [ReportController::class, 'generateInventoryReport'])->name('reports.inventory');
        Route::get('/sales', [ReportController::class, 'generateSalesReport'])->name('reports.sales');
        Route::get('/low-stock', [ReportController::class, 'generateLowStockReport'])->name('reports.low-stock');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
