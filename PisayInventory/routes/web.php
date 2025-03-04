<?php

use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReceivingController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\RolesController;

// Default route to inventory
Route::get('/', function () {
    return redirect('/inventory');
});

// Welcome page under /inventory
Route::get('/inventory', function () {
    return view('welcome');
})->name('welcome');

// Authentication Routes under /inventory
Route::middleware('guest')->group(function () {
    Route::get('inventory/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('inventory/login', [AuthController::class, 'login']);
    Route::get('inventory/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('inventory/register', [AuthController::class, 'register']);
});

// Protected Routes with /inventory Prefix
Route::middleware('auth')->group(function () {
    Route::prefix('inventory')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Items Management
        Route::resource('items', ItemController::class);
        Route::get('/items/manage', [ItemController::class, 'manage'])->name('items.manage');
        Route::post('items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');
        Route::post('items/{id}/stock-in', [ItemController::class, 'stockIn'])->name('items.stock-in');
        Route::post('items/{id}/stock-out', [ItemController::class, 'stockOut'])->name('items.stock-out');
        Route::post('/items/preview-columns', [ItemController::class, 'previewColumns'])->name('items.preview-columns');
        Route::post('/items/import', [ItemController::class, 'import'])->name('items.import');
        

        // Inventory Management
        Route::resource('inventory', InventoryController::class);
        Route::put('inventory/{id}/restore', [InventoryController::class, 'restore'])->name('inventory.restore');
        Route::post('/inventory/{id}/stockout', [InventoryController::class, 'stockout'])->name('inventory.stockout');

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
            Route::put('/units/{id}', 'update')->name('units.update');
            Route::delete('/units/{id}', 'destroy')->name('units.destroy');
            Route::post('/units/{id}/restore', 'restore')->name('units.restore');
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
        Route::resource('employees', EmployeeController::class);
        Route::post('employees/{employeeId}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
        Route::post('/employees/preview-columns', [EmployeeController::class, 'previewColumns'])->name('employees.preview-columns');
        Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.import');


        // Role Management
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::post('/roles/{id}/restore', [RoleController::class, 'restore'])->name('roles.restore');

        Route::get('roles/policies', [RoleController::class, 'policies'])->name('roles.policies');
        Route::put('roles/policies/{id}', [RoleController::class, 'updatePolicy'])->name('roles.policies.update');

        // Purchases Management
        Route::resource('purchases', PurchaseController::class);
        Route::put('purchases/{id}/restore', [PurchaseController::class, 'restore'])->name('purchases.restore');
        Route::delete('/purchases/{id}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
        Route::get('/purchases/{id}', [PurchaseController::class, 'show'])->name('purchases.show');
        Route::post('/purchases/{id}/restore', [PurchaseController::class, 'restore'])->name('purchases.restore');

        // Receiving Management
        Route::get('receiving', [ReceivingController::class, 'index'])->name('receiving.index');
        Route::get('receiving/create', [ReceivingController::class, 'create'])->name('receiving.create');
        Route::post('receiving', [ReceivingController::class, 'store'])->name('receiving.store');
        Route::get('receiving/{id}', [ReceivingController::class, 'show'])->name('receiving.show');
        Route::delete('receiving/{id}', [ReceivingController::class, 'destroy'])->name('receiving.destroy');
        Route::post('receiving/{id}/restore', [ReceivingController::class, 'restore'])->name('receiving.restore');

        // Profile Management inside /inventory
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Module Management
        Route::resource('modules', ModuleController::class);
    });
});
