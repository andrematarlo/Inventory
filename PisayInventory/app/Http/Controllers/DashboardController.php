<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Classification;
use App\Models\Supplier;
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

        // Fast Counting
        $totalItems = Item::where('IsDeleted', false)->count();
        $totalEmployees = Employee::where('IsDeleted', false)->count();
        $totalSuppliers = Supplier::where('IsDeleted', false)->count();

        // Low Stock Query Optimization
        $lowStockItems = Item::whereColumn('StocksAvailable', '<=', 'ReorderPoint')
            ->where('IsDeleted', false)
            ->select('ItemName', 'StocksAvailable', 'ReorderPoint')
            ->take(10)
            ->get();

        $lastWeek = now()->subDays(7);

        // Optimized Recent Activities
        $recentActivities = collect();

        $models = [
            'items' => Item::whereBetween('DateCreated', [$lastWeek, now()])->orWhereBetween('DateModified', [$lastWeek, now()])->orWhereBetween('DateDeleted', [$lastWeek, now()])->select('ItemId as id', 'ItemName as name', 'DateCreated', 'DateModified', 'DateDeleted', 'IsDeleted', 'CreatedById', 'ModifiedById', 'DeletedById'),
            'suppliers' => Supplier::whereBetween('DateCreated', [$lastWeek, now()])->orWhereBetween('DateModified', [$lastWeek, now()])->orWhereBetween('DateDeleted', [$lastWeek, now()])->select('SupplierId as id', 'SupplierName as name', 'DateCreated', 'DateModified', 'DateDeleted', 'IsDeleted', 'CreatedById', 'ModifiedById', 'DeletedById'),
            'employees' => Employee::whereBetween('DateCreated', [$lastWeek, now()])->orWhereBetween('DateModified', [$lastWeek, now()])->orWhereBetween('DateDeleted', [$lastWeek, now()])->select('EmployeeId as id', 'LastName as name', 'DateCreated', 'DateModified', 'DateDeleted', 'IsDeleted', 'CreatedById', 'ModifiedById', 'DeletedById'),
            'classifications' => Classification::whereBetween('DateCreated', [$lastWeek, now()])->orWhereBetween('DateModified', [$lastWeek, now()])->orWhereBetween('DateDeleted', [$lastWeek, now()])->select('ClassificationId as id', 'ClassificationName as name', 'DateCreated', 'DateModified', 'DateDeleted', 'IsDeleted', 'CreatedById', 'ModifiedById', 'DeletedById'),
            'units' => Unit::whereBetween('DateCreated', [$lastWeek, now()])->orWhereBetween('DateModified', [$lastWeek, now()])->orWhereBetween('DateDeleted', [$lastWeek, now()])->select('UnitOfMeasureId as id', 'UnitName as name', 'DateCreated', 'DateModified', 'DateDeleted', 'IsDeleted', 'CreatedById', 'ModifiedById', 'DeletedById'),
        ];

        foreach ($models as $key => $query) {
            $activities = $query->take(10)->get();
            foreach ($activities as $activity) {
                $recentActivities->push([
                    'type' => $key,
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'created_at' => $activity->DateCreated,
                    'modified_at' => $activity->DateModified,
                    'deleted_at' => $activity->DateDeleted,
                    'is_deleted' => $activity->IsDeleted,
                    'created_by' => $activity->CreatedById,
                    'modified_by' => $activity->ModifiedById,
                    'deleted_by' => $activity->DeletedById
                ]);
            }
        }

        // Sort by most recent activity
        $recentActivities = $recentActivities->sortByDesc(function ($activity) {
            return max(strtotime($activity['created_at'] ?? 0), strtotime($activity['modified_at'] ?? 0), strtotime($activity['deleted_at'] ?? 0));
        })->take(10);

        return view('dashboard.index', compact('totalItems', 'totalEmployees', 'totalSuppliers', 'lowStockItems', 'recentActivities'));
    }
}
