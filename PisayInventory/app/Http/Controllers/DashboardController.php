<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Classification;
use App\Models\Supplier;
use App\Models\Inventory;
use App\Models\Employee;
use App\Models\Unit; // Add this line
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
        
                // Get counts and data for dashboard
                $totalItems = Item::where('IsDeleted', false)->count();
                $totalEmployees = Employee::where('IsDeleted', false)->count();
                $totalSuppliers = Supplier::where('IsDeleted', false)->count();
                
                // Get items with low stock
                $lowStockItems = Item::where('IsDeleted', false)
                    ->whereRaw('StocksAvailable <= ReorderPoint')
                    ->get();
        
                $lastWeek = now()->subDays(7);
        
                    // Get activities from Items
    $itemActivities = Item::with(['classification', 'created_by_user', 'modified_by_user', 'deleted_by_user'])
    ->where(function($query) use ($lastWeek) {
        $query->where('DateCreated', '>=', $lastWeek)
              ->orWhere('DateModified', '>=', $lastWeek)
              ->orWhere('DateDeleted', '>=', $lastWeek);
    })
    ->get()
    ->map(function($item) {
        $item->entity_type = 'item';
        return $item;
    });

// Get activities from Suppliers
$supplierActivities = Supplier::with(['created_by_user', 'modified_by_user', 'deleted_by_user'])
    ->where(function($query) use ($lastWeek) {
        $query->where('DateCreated', '>=', $lastWeek)
              ->orWhere('DateModified', '>=', $lastWeek)
              ->orWhere('DateDeleted', '>=', $lastWeek);
    })
    ->get()
    ->map(function($supplier) {
        $supplier->entity_type = 'supplier';
        return $supplier;
    });

// Get activities from Employees
$employeeActivities = Employee::with(['creator', 'modifier', 'deleter'])
    ->where(function($query) use ($lastWeek) {
        $query->where('DateCreated', '>=', $lastWeek)
              ->orWhere('DateModified', '>=', $lastWeek)
              ->orWhere('DateDeleted', '>=', $lastWeek);
    })
    ->get()
    ->map(function($employee) {
        $employee->entity_type = 'employee';
        return $employee;
    });

// Get activities from Classifications
$classificationActivities = Classification::with(['created_by_user', 'modified_by_user', 'deleted_by_user'])
    ->where(function($query) use ($lastWeek) {
        $query->where('DateCreated', '>=', $lastWeek)
              ->orWhere('DateModified', '>=', $lastWeek)
              ->orWhere('DateDeleted', '>=', $lastWeek);
    })
    ->get()
    ->map(function($classification) {
        $classification->entity_type = 'classification';
        return $classification;
    });

// Get activities from Units
$unitActivities = Unit::with(['createdBy', 'modifiedBy', 'deletedBy'])
    ->where(function($query) use ($lastWeek) {
        $query->where('DateCreated', '>=', $lastWeek)
              ->orWhere('DateModified', '>=', $lastWeek)
              ->orWhere('DateDeleted', '>=', $lastWeek);
    })
    ->get()
    ->map(function($unit) {
        $unit->entity_type = 'unit';
        return $unit;
    });

// Merge all activities
$recentActivities = collect()
    ->concat($itemActivities)
    ->concat($supplierActivities)
    ->concat($employeeActivities)
    ->concat($classificationActivities)
    ->concat($unitActivities)
    ->sortByDesc(function ($activity) {
        // Get the most recent action date
        $dates = collect([
            $activity->DateCreated,
            $activity->DateModified,
            $activity->DateDeleted
        ])->filter();
        return $dates->max();
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