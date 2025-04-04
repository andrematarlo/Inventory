<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\ClassificationController;
use App\Http\Controllers\Api\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API routes for Items
Route::middleware('auth:sanctum')->group(function () {
    // Items API
    Route::get('/items', [ItemController::class, 'index']);
    Route::get('/items/{id}', [ItemController::class, 'show']);
    Route::post('/items', [ItemController::class, 'store']);
    Route::put('/items/{id}', [ItemController::class, 'update']);
    Route::delete('/items/{id}', [ItemController::class, 'destroy']);

    // Suppliers API
    Route::get('/suppliers', [SupplierController::class, 'index']);
    Route::get('/suppliers/{id}', [SupplierController::class, 'show']);
    Route::post('/suppliers', [SupplierController::class, 'store']);
    Route::put('/suppliers/{id}', [SupplierController::class, 'update']);
    Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy']);

    // Classifications API
    Route::get('/classifications', [ClassificationController::class, 'index']);
    Route::get('/classifications/{id}', [ClassificationController::class, 'show']);
    Route::post('/classifications', [ClassificationController::class, 'store']);
    Route::put('/classifications/{id}', [ClassificationController::class, 'update']);
    Route::delete('/classifications/{id}', [ClassificationController::class, 'destroy']);

    // Reports API
    Route::get('/reports/inventory', [ReportController::class, 'inventory']);
    Route::get('/reports/low-stock', [ReportController::class, 'lowStock']);
    Route::get('/reports/sales', [ReportController::class, 'sales']);
});
