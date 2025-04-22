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
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\LaboratoriesController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\LaboratoryReservationController;
use App\Http\Controllers\EquipmentBorrowingController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\POS\OrderController;
use App\Http\Controllers\POS\DepositController as POSDepositController;
use App\Http\Controllers\POS\CashierController;
use App\Http\Controllers\MenuItemController;
use App\Models\MenuItem;
use App\Models\Classification;
use App\Http\Controllers\DeleteFormController;
use App\Http\Controllers\LaboratoryAccountabilityController;
use App\Http\Controllers\LaboratoryReagentController;

// Add this at the top of your routes to debug
Route::get('/debug/routes', function() {
    $routes = Route::getRoutes();
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'borrowings')) {
            dump($route->uri() . ' - ' . $route->getName());
        }
    }
    
    // Debugging menu-items routes
    $menuItemRoutes = collect(Route::getRoutes())->map(function ($route) {
        return [
            'URI' => $route->uri(),
            'Name' => $route->getName(),
            'Method' => implode('|', $route->methods()),
        ];
    })->filter(function ($route) {
        return str_contains($route['URI'], 'menu-items');
    })->all();
    
    dump($menuItemRoutes);
    
    return "Routes debugging completed";
});

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

                          // Students Management
                          Route::get('students/trash', [StudentsController::class, 'trash'])->name('students.trash');
                          Route::post('students/{id}/restore', [StudentsController::class, 'restore'])->name('students.restore');
                          Route::delete('students/{id}/force-delete', [StudentsController::class, 'forceDelete'])->name('students.force-delete');
                          Route::resource('students', StudentsController::class);
                          Route::get('/students/import', [StudentsController::class, 'showImport'])->name('students.import');
                          Route::post('/students/preview-columns', [StudentsController::class, 'previewColumns'])->name('students.preview-columns');
                          Route::post('/students/process-import', [StudentsController::class, 'processImport'])->name('students.process-import');
             
        

        // Items Management
        Route::resource('items', ItemController::class);
        Route::get('/items/manage', [ItemController::class, 'manage'])->name('items.manage');
        Route::post('items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');
        Route::get('items/{id}/restore', [ItemController::class, 'restore']);
        Route::post('items/{id}/stock-in', [ItemController::class, 'stockIn'])->name('items.stock-in');
        Route::post('items/{id}/stock-out', [ItemController::class, 'stockOut'])->name('items.stock-out');
        Route::post('/items/preview-columns', [ItemController::class, 'previewColumns'])->name('items.preview-columns');
        Route::post('/items/import', [ItemController::class, 'import'])->name('items.import');
        Route::post('/items/export', [ItemController::class, 'export'])->name('items.export');
        Route::get('/items/search', [ItemController::class, 'search'])->name('items.search');
        Route::delete('/inventory/items/{id}', [ItemController::class, 'destroy'])->name('items.destroy');

        // Inventory Management
        Route::resource('inventory', InventoryController::class);
        Route::put('inventory/{id}/restore', [InventoryController::class, 'restore'])->name('inventory.restore');
        Route::post('/inventory/{id}/stockout', [InventoryController::class, 'stockout'])->name('inventory.stockout');
        Route::delete('inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

        // Suppliers Management
        Route::prefix('suppliers')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('suppliers.index');
            Route::post('/store', [SupplierController::class, 'store'])->name('suppliers.store');
            Route::delete('/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
            Route::post('/{id}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        });

        // Classifications Management
        Route::post('classifications/{id}/restore', [ClassificationController::class, 'restore'])->name('classifications.restore');
        Route::delete('classifications/{id}', [ClassificationController::class, 'destroy'])->name('classifications.destroy');
        Route::resource('classifications', ClassificationController::class);

        // Units Management
        Route::controller(UnitController::class)->group(function () {
            Route::get('/units', 'index')->name('units.index');
            Route::get('/units/trash', 'trash')->name('units.trash');
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
        Route::post('/employees/export', [EmployeeController::class, 'export'])->name('employees.export');


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

        // Point of Sale Routes
        Route::prefix('pos')->name('pos.')->group(function () {
            // Main POS routes
            Route::get('/', [POSController::class, 'index'])->name('index');
            Route::get('/create', [POSController::class, 'create'])->name('create');
            Route::post('/store', [POSController::class, 'store'])->name('store');
            Route::get('/show/{id}', [POSController::class, 'show'])->name('show');
            Route::match(['post', 'patch'], '/process/{order}', [POSController::class, 'process'])->name('process');
            Route::match(['post', 'patch'], '/process-by-id/{id}', [POSController::class, 'processById'])->name('process.by.id');
            Route::post('/cancel/{id}', [POSController::class, 'cancel'])->name('cancel');
            Route::get('/order-details/{id}', [POSController::class, 'getOrderDetails'])->name('order-details');
            Route::get('/search-students', [POSController::class, 'searchStudents'])->name('search-students');
            Route::get('/student-balance/{studentId}', [POSController::class, 'getStudentBalance'])->name('student-balance');
            Route::get('/check-stock/{id}', [POSController::class, 'checkStock'])->name('check-stock');
            Route::get('/check-student-balance/{studentId}', [POSController::class, 'checkStudentBalance'])->name('check-student-balance');
            
            // Orders routes
            Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/create', function() {
                $menuItems = MenuItem::where('IsDeleted', false)
                    ->where('IsAvailable', true)
                    ->with('classification')
                    ->get();
                
                $categories = Classification::all();
                
                return view('pos.create', compact('menuItems', 'categories'));
            })->name('orders.create');
            Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
            Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
            Route::get('/orders/{id}/items', [OrderController::class, 'getItems'])->name('orders.items');
            Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
            Route::post('/orders/{id}/claim', [OrderController::class, 'claim'])->name('orders.claim');
            Route::get('/orders/{id}/print', [OrderController::class, 'print'])->name('orders.print');
            
            // Cashier routes
            Route::prefix('cashier')->name('cashier.')->group(function () {
                Route::get('/', [CashierController::class, 'index'])->name('index');
                Route::get('/create', [CashierController::class, 'create'])->name('create');
                Route::get('/{order}', [CashierController::class, 'show'])->name('show');
            });
            
            // Cashiering routes
            Route::get('/cashiering', [POSController::class, 'cashiering'])->name('cashiering');
            Route::get('/orders/{id}/process-payment', [POSController::class, 'processPayment'])->name('process-payment');
            Route::post('/orders/{id}/process-payment', [POSController::class, 'postProcessPayment'])->name('post-payment');
            Route::get('/cancel-order/{id}', [POSController::class, 'cancelOrder'])->name('cancel-order');
            Route::get('/orders/{id}/details', [OrderController::class, 'getDetails'])->name('order.details');
            Route::get('/student-balance/{id}', [StudentsController::class, 'getBalance'])->name('student.balance');
            
            // Add cashier routes
            Route::get('/cashier', [POSController::class, 'cashiering'])->name('cashier.index');
            
            // Category filtering
            Route::get('/filter-by-category/{categoryId?}', [POSController::class, 'filterByCategory'])->name('filter-by-category');
            
            // Reports
            Route::get('/reports', [POSController::class, 'reports'])->name('reports');
            Route::get('/reports/sales', [POSController::class, 'salesReport'])->name('reports.sales');
            Route::get('/reports/deposits', [POSController::class, 'depositsReport'])->name('reports.deposits');
            
            // Reports grouped properly
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [POSController::class, 'reports'])->name('index');
                Route::get('/sales', [POSController::class, 'salesReport'])->name('sales');
                Route::get('/deposits', [POSController::class, 'depositsReport'])->name('deposits');
            });
            
            // Deposits routes
            Route::get('/deposits', [POSController::class, 'index'])->name('deposits.index');
            Route::post('/deposits/store', [POSController::class, 'storeDeposit'])->name('deposits.store');
            Route::post('/deposits/{id}/approve', [POSController::class, 'approveDeposit'])->name('deposits.approve');
            Route::post('/deposits/{id}/reject', [POSController::class, 'rejectDeposit'])->name('deposits.reject');
            
            // Menu Items Management Routes
            Route::prefix('menu-items')->name('menu-items.')->group(function () {
                Route::get('/', [POSController::class, 'menuItems'])->name('index');
                Route::get('/create', [POSController::class, 'createMenuItem'])->name('create');
                Route::post('/', [POSController::class, 'storeMenuItem'])->name('store');
                Route::get('/{id}/edit', [POSController::class, 'editMenuItem'])->name('edit');
                Route::put('/{id}', [POSController::class, 'updateMenuItem'])->name('update');
                Route::delete('/{id}', [POSController::class, 'deleteMenuItem'])->name('destroy');
                Route::post('/{id}/toggle-availability', [POSController::class, 'toggleMenuItemAvailability'])->name('toggle-availability');
                Route::post('/{id}/restore', [POSController::class, 'restoreMenuItem'])->name('restore');
            });

            // Add these routes for edit and delete functionality
            Route::get('/orders/{id}/edit', [OrderController::class, 'edit'])->name('orders.edit');
            Route::put('/orders/{id}', [OrderController::class, 'update'])->name('orders.update');
            Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');

            // New route for checking stock levels
            Route::get('/check-stock/{id}', [POSController::class, 'checkStock'])->name('check-stock');

            Route::post('/orders/{id}', [POSController::class, 'updateOrder'])->name('pos.orders.update');
        });

        // Laboratory Management Routes
        Route::prefix('laboratories')->group(function () {
            Route::get('/', [LaboratoriesController::class, 'index'])->name('laboratories.index');
            Route::get('/next-id', [LaboratoriesController::class, 'getNextId'])->name('laboratories.getNextId');
            Route::get('/create', [LaboratoriesController::class, 'create'])->name('laboratories.create');
            Route::post('/', [LaboratoriesController::class, 'store'])->name('laboratories.store');
            Route::get('/{id}', [LaboratoriesController::class, 'show'])->name('laboratories.show')->where('id', '.*');
            Route::get('/{id}/edit', [LaboratoriesController::class, 'edit'])->name('laboratories.edit')->where('id', '.*');
            Route::put('/{id}', [LaboratoriesController::class, 'update'])->name('laboratories.update')->where('id', '.*');
            Route::delete('/{id}', [LaboratoriesController::class, 'destroy'])->name('laboratories.destroy')->where('id', '.*');
            Route::put('/{id}/restore', [LaboratoriesController::class, 'restore'])->name('laboratories.restore')->where('id', '.*');
        });

        // Equipment routes
        Route::prefix('equipment')->group(function () {
            // The restore route must come BEFORE other routes
            Route::post('/{equipment}/restore', [EquipmentController::class, 'restore'])->name('equipment.restore');
            
            // Basic CRUD routes
            Route::get('/', [EquipmentController::class, 'index'])->name('equipment.index');
            Route::get('/create', [EquipmentController::class, 'create'])->name('equipment.create');
            Route::post('/', [EquipmentController::class, 'store'])->name('equipment.store');
            Route::get('/{equipment}', [EquipmentController::class, 'show'])->name('equipment.show');
            Route::get('/{equipment}/edit', [EquipmentController::class, 'edit'])->name('equipment.edit');
            Route::put('/{equipment}', [EquipmentController::class, 'update'])->name('equipment.update');
            Route::delete('/{equipment}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');
        });

        // Equipment Borrowing routes
        Route::prefix('equipment-borrowings')->group(function () {
            Route::get('/', [EquipmentBorrowingController::class, 'index'])->name('equipment.borrowings');
            Route::get('/create', [EquipmentBorrowingController::class, 'create'])->name('equipment.borrowings.create');
            Route::post('/', [EquipmentBorrowingController::class, 'store'])->name('equipment.borrowings.store');
            Route::get('/{borrowing}', [EquipmentBorrowingController::class, 'show'])->name('equipment.borrowings.show');
            Route::get('/{borrowing}/edit', [EquipmentBorrowingController::class, 'edit'])->name('equipment.borrowings.edit');
            Route::put('/{borrowing}', [EquipmentBorrowingController::class, 'update'])->name('equipment.borrowings.update');
            Route::delete('/{borrowing}', [EquipmentBorrowingController::class, 'destroy'])->name('equipment.borrowings.destroy');
            Route::post('/{borrowing}/return', [EquipmentBorrowingController::class, 'return'])->name('equipment.borrowings.return');
            Route::post('/{borrowing}/restore', [EquipmentBorrowingController::class, 'restore'])->name('equipment.borrowings.restore');
        });

        // Laboratory Reservations
        Route::prefix('laboratory')->name('laboratory.')->group(function () {
            // Reservations
            Route::get('reservations', [LaboratoryReservationController::class, 'index'])->name('reservations');
            Route::get('reservations/data', [LaboratoryReservationController::class, 'data'])->name('reservations.data');
            Route::get('reservations/counts', [LaboratoryReservationController::class, 'getStatusCounts'])->name('reservations.counts');
            Route::get('reservations/create', [LaboratoryReservationController::class, 'create'])->name('reservations.create');
            Route::post('reservations', [LaboratoryReservationController::class, 'store'])->name('reservations.store');
            Route::get('reservations/teachers', [LaboratoryReservationController::class, 'getTeachers'])->name('reservations.getTeachers');
            Route::get('reservations/student-info', [LaboratoryReservationController::class, 'getStudentInfo'])->name('reservations.getStudentInfo');
            Route::get('reservations/generate-control-no', [LaboratoryReservationController::class, 'generateControlNo'])->name('reservations.generateControlNo');
            Route::post('reservations/check-conflicts', [LaboratoryReservationController::class, 'checkConflicts'])->name('reservations.checkConflicts');
            Route::post('reservations/{id}/endorse', [LaboratoryReservationController::class, 'endorse'])->name('reservations.endorse');
            Route::delete('reservations/{id}', [LaboratoryReservationController::class, 'destroy'])->name('reservations.destroy')->where('id', '.*');
            Route::get('reservations/{id}', [LaboratoryReservationController::class, 'show'])->name('reservations.show')->where('id', '.*');
            Route::post('reservations/{id}/disapprove', [LaboratoryReservationController::class, 'disapprove'])->name('reservations.disapprove');
            Route::post('reservations/{id}/approve', [LaboratoryReservationController::class, 'approve'])->name('reservations.approve');
            Route::post('reservations/{id}/restore', [LaboratoryReservationController::class, 'restore'])->name('reservations.restore');
            
            // Accountability
            Route::get('accountability', [LaboratoryAccountabilityController::class, 'index'])->name('accountability');
            Route::get('accountability/index', [LaboratoryAccountabilityController::class, 'index'])->name('accountability.index');
            Route::get('accountability/create', [LaboratoryAccountabilityController::class, 'create'])->name('accountability.create');
            Route::post('accountability', [LaboratoryAccountabilityController::class, 'store'])->name('accountability.store');
            Route::get('accountability/{id}', [LaboratoryAccountabilityController::class, 'show'])->name('accountability.show');
            Route::post('accountability/{id}/approve', [LaboratoryAccountabilityController::class, 'approve'])->name('accountability.approve');
            Route::post('accountability/{id}/reject', [LaboratoryAccountabilityController::class, 'reject'])->name('accountability.reject');
            Route::post('accountability/{id}/delete', [LaboratoryAccountabilityController::class, 'delete'])->name('accountability.delete');
            
            // Reagent
            Route::get('reagent', [LaboratoryReagentController::class, 'index'])->name('reagent');
            Route::get('reagent/index', [LaboratoryReagentController::class, 'index'])->name('reagent.index');
            Route::get('reagent/create', [LaboratoryReagentController::class, 'create'])->name('reagent.create');
            Route::post('reagent', [LaboratoryReagentController::class, 'store'])->name('reagent.store');
            Route::get('reagent/{id}', [LaboratoryReagentController::class, 'show'])->name('reagent.show');
            Route::post('reagent/{id}/approve', [LaboratoryReagentController::class, 'approve'])->name('reagent.approve');
            Route::post('reagent/{id}/reject', [LaboratoryReagentController::class, 'reject'])->name('reagent.reject');
            Route::post('reagent/{id}/delete', [LaboratoryReagentController::class, 'delete'])->name('reagent.delete');
            

        });
    }); // Close inventory prefix
}); // Close auth middleware

// Role Policy routes
Route::post('/roles/policies/create', [RoleController::class, 'createPolicy'])->name('roles.policies.create');

// Equipment restore route
Route::post('/equipment/{equipment}/restore', [EquipmentController::class, 'restore'])->name('equipment.restore');

// Direct routes for equipment borrowings at root level
Route::get('/equipment-borrowings/{id}', [EquipmentBorrowingController::class, 'show'])
    ->name('equipment.borrowings.direct.show')
    ->where('id', '.*');
Route::post('/equipment-borrowings/{id}/restore', [EquipmentBorrowingController::class, 'restore'])
    ->name('equipment.borrowings.direct.restore')
    ->where('id', '.*');
Route::get('/equipment-borrowings/{id}/edit', [EquipmentBorrowingController::class, 'edit'])
    ->name('equipment.borrowings.direct.edit')
    ->where('id', '.*');
Route::put('/equipment-borrowings/{id}', [EquipmentBorrowingController::class, 'update'])
    ->name('equipment.borrowings.direct.update')
    ->where('id', '.*');
Route::delete('/equipment-borrowings/{id}', [EquipmentBorrowingController::class, 'destroy'])
    ->name('equipment.borrowings.direct.destroy')
    ->where('id', '.*');
Route::post('/equipment-borrowings/{id}/return', [EquipmentBorrowingController::class, 'return'])
    ->name('equipment.borrowings.direct.return')
    ->where('id', '.*');

// Add these routes for restoring laboratories (supporting both PUT and POST)
Route::put('/inventory/laboratories/{laboratory}/restore', [App\Http\Controllers\LaboratoriesController::class, 'restore'])
    ->name('laboratories.restore')
    ->where('laboratory', '.*'); // This allows any character in the ID

Route::post('/inventory/laboratories/{laboratory}/restore', [App\Http\Controllers\LaboratoriesController::class, 'restore'])
    ->name('laboratories.restore.post')
    ->where('laboratory', '.*');

// Add this test route for debugging laboratory restore
Route::get('/debug/laboratory/{id}', function($id) {
    try {
        // Try to find the laboratory
        $lab = \App\Models\Laboratory::withTrashed()->where('laboratory_id', $id)->first();
        
        if (!$lab) {
            return response()->json([
                'success' => false,
                'message' => 'Laboratory not found with ID: ' . $id,
                'all_labs' => \App\Models\Laboratory::withTrashed()->get(['laboratory_id', 'laboratory_name', 'deleted_at'])->toArray()
            ]);
        }
        
        return response()->json([
            'success' => true,
            'laboratory' => $lab->toArray(),
            'is_deleted' => $lab->trashed(),
            'deleted_at' => $lab->deleted_at
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->where('id', '.*');

Route::get('/pos/search-students', [POSController::class, 'searchStudents'])->name('pos.search-students');

// Orders Dashboard
Route::get('/pos/dashboard', [OrderController::class, 'dashboard'])->name('pos.dashboard');
Route::get('/pos/orders/{id}/items', [OrderController::class, 'getOrderItems'])->name('pos.orders.items');
Route::post('/pos/orders/{id}/process', [OrderController::class, 'processById'])->name('pos.orders.process');
Route::post('/pos/orders/{id}/claim', [OrderController::class, 'claim'])->name('pos.orders.claim');

// POS Reports Routes
Route::get('/pos/reports', [POSController::class, 'reports'])->name('pos.reports');
Route::get('/pos/reports/sales', [POSController::class, 'salesReport'])->name('pos.reports.sales');
Route::get('/pos/reports/deposits', [POSController::class, 'depositsReport'])->name('pos.reports.deposits');

// Student POS Order History Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/pos/my-orders', [App\Http\Controllers\POS\StudentOrderController::class, 'index'])->name('pos.student.orders');
    Route::get('/pos/my-orders/{orderId}/items', [App\Http\Controllers\POS\StudentOrderController::class, 'getItems'])->name('pos.student.orders.items');
});