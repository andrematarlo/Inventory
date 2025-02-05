<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Classification;
use App\Models\Supplier;
use App\Models\Inventory;
use App\Models\Employee;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
{
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    
    // Keep existing counts
    $totalItems = Item::where('IsDeleted', false)->count();
    $totalEmployees = Employee::where('IsDeleted', false)->count();
    $totalSuppliers = Supplier::where('IsDeleted', false)->count();
    
    // Keep low stock items
    $lowStockItems = Item::where('IsDeleted', false)
        ->whereRaw('StocksAvailable <= ReorderPoint')
        ->get();

    $lastWeek = now()->subDays(7);

    // Updated activities collection
    $itemActivities = Item::with(['classification', 'created_by_user', 'modified_by_user', 'deleted_by_user'])
        ->where(function($query) use ($lastWeek) {
            $query->where('DateCreated', '>=', $lastWeek)
                  ->orWhere('DateModified', '>=', $lastWeek)
                  ->orWhere('DateDeleted', '>=', $lastWeek);
        })
        ->get()
        ->map(function($item) {
            return [
                'type' => 'item',
                'name' => $item->ItemName,
                'created_at' => $item->DateCreated,
                'modified_at' => $item->DateModified,
                'deleted_at' => $item->DateDeleted,
                'created_by' => $item->created_by_user->Username ?? 'System',
                'modified_by' => $item->modified_by_user->Username ?? 'System',
                'deleted_by' => $item->deleted_by_user->Username ?? 'System',
                'is_deleted' => $item->IsDeleted,
                'details' => $item->classification ? "({$item->classification->ClassificationName})" : ''
            ];
        });

    $supplierActivities = Supplier::with(['created_by_user', 'modified_by_user', 'deleted_by_user'])
        ->where(function($query) use ($lastWeek) {
            $query->where('DateCreated', '>=', $lastWeek)
                  ->orWhere('DateModified', '>=', $lastWeek)
                  ->orWhere('DateDeleted', '>=', $lastWeek);
        })
        ->get()
        ->map(function($supplier) {
            return [
                'type' => 'supplier',
                'name' => $supplier->SupplierName,
                'created_at' => $supplier->DateCreated,
                'modified_at' => $supplier->DateModified,
                'deleted_at' => $supplier->DateDeleted,
                'created_by' => $supplier->created_by_user->Username ?? 'System',
                'modified_by' => $supplier->modified_by_user->Username ?? 'System',
                'deleted_by' => $supplier->deleted_by_user->Username ?? 'System',
                'is_deleted' => $supplier->IsDeleted,
                'details' => $supplier->ContactNum ? "(Contact: {$supplier->ContactNum})" : ''
            ];
        });

    $employeeActivities = Employee::with(['createdBy', 'modifiedBy', 'deletedBy'])
        ->where(function($query) use ($lastWeek) {
            $query->where('DateCreated', '>=', $lastWeek)
                  ->orWhere('DateModified', '>=', $lastWeek)
                  ->orWhere('DateDeleted', '>=', $lastWeek);
        })
        ->get()
        ->map(function($employee) {
            return [
                'type' => 'employee',
                'name' => "{$employee->FirstName} {$employee->LastName}",
                'created_at' => $employee->DateCreated,
                'modified_at' => $employee->DateModified,
                'deleted_at' => $employee->DateDeleted,
                'created_by' => optional($employee->createdBy)->Username ?? 'System',
                'modified_by' => optional($employee->modifiedBy)->Username ?? 'System',
                'deleted_by' => optional($employee->deletedBy)->Username ?? 'System',
                'is_deleted' => $employee->IsDeleted,
                'details' => $employee->Email ? "(Email: {$employee->Email})" : ''
            ];
        });

    $classificationActivities = Classification::with(['created_by_user', 'modified_by_user', 'deleted_by_user'])
        ->where(function($query) use ($lastWeek) {
            $query->where('DateCreated', '>=', $lastWeek)
                  ->orWhere('DateModified', '>=', $lastWeek)
                  ->orWhere('DateDeleted', '>=', $lastWeek);
        })
        ->get()
        ->map(function($classification) {
            return [
                'type' => 'classification',
                'name' => $classification->ClassificationName,
                'created_at' => $classification->DateCreated,
                'modified_at' => $classification->DateModified,
                'deleted_at' => $classification->DateDeleted,
                'created_by' => $classification->created_by_user->Username ?? 'System',
                'modified_by' => $classification->modified_by_user->Username ?? 'System',
                'deleted_by' => $classification->deleted_by_user->Username ?? 'System',
                'is_deleted' => $classification->IsDeleted,
                'details' => ''
            ];
        });

    $unitActivities = Unit::with(['createdBy', 'modifiedBy', 'deletedBy'])
        ->where(function($query) use ($lastWeek) {
            $query->where('DateCreated', '>=', $lastWeek)
                  ->orWhere('DateModified', '>=', $lastWeek)
                  ->orWhere('DateDeleted', '>=', $lastWeek);
        })
        ->get()
        ->map(function($unit) {
            return [
                'type' => 'unit',
                'name' => $unit->UnitName,
                'created_at' => $unit->DateCreated,
                'modified_at' => $unit->DateModified,
                'deleted_at' => $unit->DateDeleted,
                'created_by' => optional($unit->createdBy)->Username ?? 'System',
                'modified_by' => optional($unit->modifiedBy)->Username ?? 'System',
                'deleted_by' => optional($unit->deletedBy)->Username ?? 'System',
                'is_deleted' => $unit->IsDeleted,
                'details' => ''
            ];
        });

    // Merge and sort activities
    $recentActivities = collect()
        ->concat($itemActivities)
        ->concat($supplierActivities)
        ->concat($employeeActivities)
        ->concat($classificationActivities)
        ->concat($unitActivities)
        ->sortByDesc(function ($activity) {
            return max(
                strtotime($activity['created_at'] ?? 0),
                strtotime($activity['modified_at'] ?? 0),
                strtotime($activity['deleted_at'] ?? 0)
            );
        })
        ->take(10);

    return view('dashboard.index', compact(
        'totalItems',
        'totalEmployees',
        'totalSuppliers',
        'lowStockItems',
        'recentActivities'
    ));
}

    private function getActionType($type)
    {
        return match ($type) {
            'IN' => 'Stock In',
            'OUT' => 'Stock Out',
            default => 'Unknown'
        };
    }

    private function getActionColor($type)
    {
        return match ($type) {
            'IN' => 'success',
            'OUT' => 'danger',
            default => 'secondary'
        };
    }
}