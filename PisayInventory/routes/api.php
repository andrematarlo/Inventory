<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    Route::get('/items', [App\Http\Controllers\Api\ItemController::class, 'index']);
    Route::get('/items/{id}', [App\Http\Controllers\Api\ItemController::class, 'show']);
    Route::post('/items', [App\Http\Controllers\Api\ItemController::class, 'store']);
    Route::put('/items/{id}', [App\Http\Controllers\Api\ItemController::class, 'update']);
    Route::delete('/items/{id}', [App\Http\Controllers\Api\ItemController::class, 'destroy']);

    // Suppliers API
    Route::get('/suppliers', [App\Http\Controllers\Api\SupplierController::class, 'index']);
    Route::get('/suppliers/{id}', [App\Http\Controllers\Api\SupplierController::class, 'show']);
    Route::post('/suppliers', [App\Http\Controllers\Api\SupplierController::class, 'store']);
    Route::put('/suppliers/{id}', [App\Http\Controllers\Api\SupplierController::class, 'update']);
    Route::delete('/suppliers/{id}', [App\Http\Controllers\Api\SupplierController::class, 'destroy']);

    // Classifications API
    Route::get('/classifications', [App\Http\Controllers\Api\ClassificationController::class, 'index']);
    Route::get('/classifications/{id}', [App\Http\Controllers\Api\ClassificationController::class, 'show']);
    Route::post('/classifications', [App\Http\Controllers\Api\ClassificationController::class, 'store']);
    Route::put('/classifications/{id}', [App\Http\Controllers\Api\ClassificationController::class, 'update']);
    Route::delete('/classifications/{id}', [App\Http\Controllers\Api\ClassificationController::class, 'destroy']);

    // Reports API
    Route::get('/reports/inventory', [App\Http\Controllers\Api\ReportController::class, 'inventory']);
    Route::get('/reports/low-stock', [App\Http\Controllers\Api\ReportController::class, 'lowStock']);
    Route::get('/reports/sales', [App\Http\Controllers\Api\ReportController::class, 'sales']);
});
