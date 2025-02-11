<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PurchaseController; // Added PurchaseController
use App\Http\Controllers\ReceivingController;
use Illuminate\Support\Facades\Route;


// Welcome route
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
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Items Management
    Route::resource('items', ItemController::class);
    Route::get('/items/manage', [ItemController::class, 'manage'])->name('items.manage');
    Route::post('items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');
    Route::post('items/{id}/stock-in', [ItemController::class, 'stockIn'])->name('items.stock-in');
    Route::post('items/{id}/stock-out', [ItemController::class, 'stockOut'])->name('items.stock-out');

    // Inventory Management
    Route::resource('inventory', InventoryController::class);
    Route::put('inventory/{id}/restore', [InventoryController::class, 'restore'])->name('inventory.restore');

    // Suppliers Management
    Route::resource('suppliers', SupplierController::class);
    Route::post('suppliers/{id}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');

    // Classifications Management
    Route::resource('classifications', ClassificationController::class);
    Route::get('classifications/trash', [ClassificationController::class, 'trash'])->name('classifications.trash');
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
        Route::get('/inventory/pdf', [ReportController::class, 'generateInventoryPDF'])->name('reports.inventory.pdf');
        Route::get('/sales', [ReportController::class, 'generateSalesReport'])->name('reports.sales');
        Route::get('/low-stock', [ReportController::class, 'generateLowStockReport'])->name('reports.low-stock');
        Route::get('/generate', [ReportController::class, 'generate'])->name('reports.generate');
    });

    // Employee Management
    Route::get('employees/{employeeId}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('employees/{employeeId}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('employees/{employeeId}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::post('employees/{employeeId}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
    Route::resource('employees', EmployeeController::class)->except(['edit', 'update', 'destroy']);

    // Role Management
    Route::resource('roles', RoleController::class, ['except' => ['show']]);
    Route::get('roles/policies', [RoleController::class, 'policies'])->name('roles.policies');
    Route::put('roles/policies/{id}', [RoleController::class, 'updatePolicy'])->name('roles.policies.update');
    Route::post('roles/{id}/restore', [RoleController::class, 'restore'])->name('roles.restore');

    // Purchases Management
    Route::resource('purchases', PurchaseController::class);
    Route::put('purchases/{id}/restore', [PurchaseController::class, 'restore'])->name('purchases.restore');

    // Stock Management Routes
    // Route::post('/items/{item}/stock-out', [ItemController::class, 'stockOut'])->name('items.stock-out');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Receiving Management
Route::middleware(['auth'])->group(function () {
    Route::get('receiving', [ReceivingController::class, 'index'])->name('receiving.index');
    Route::get('receiving/create', [ReceivingController::class, 'create'])->name('receiving.create');
    Route::post('receiving', [ReceivingController::class, 'store'])->name('receiving.store');
    Route::get('receiving/{id}', [ReceivingController::class, 'show'])->name('receiving.show');
    Route::delete('receiving/{id}', [ReceivingController::class, 'destroy'])->name('receiving.destroy');
    Route::post('receiving/{id}/restore', [ReceivingController::class, 'restore'])->name('receiving.restore');
});
