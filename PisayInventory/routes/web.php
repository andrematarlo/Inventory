<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

// Welcome page as the default route
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected routes - all routes that require authentication
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Items Management
    Route::resource('items', ItemController::class);
    Route::get('/items/manage', [ItemController::class, 'manage'])->name('items.manage');

    // Inventory Management
    Route::resource('inventory', InventoryController::class);

    // Suppliers Management
    Route::resource('suppliers', SupplierController::class);
    Route::post('suppliers/{id}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');

    // Classifications Management
    Route::resource('classifications', ClassificationController::class);
    Route::post('classifications/{id}/restore', [ClassificationController::class, 'restore'])->name('classifications.restore');

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

    // Employee Management
    Route::resource('employees', EmployeeController::class);
    Route::post('employees/{id}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');

    // Role Management
    Route::resource('roles', RoleController::class, ['except' => ['show']]);
    Route::get('roles/policies', [RoleController::class, 'policies'])->name('roles.policies');
    Route::put('roles/policies/{id}', [RoleController::class, 'updatePolicy'])->name('roles.policies.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
