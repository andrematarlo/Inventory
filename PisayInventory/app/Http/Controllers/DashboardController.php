<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Classification;
use App\Models\Supplier;
use App\Models\Inventory;
use App\Models\Employee;
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
        
        // Get items with low stock (where StocksAvailable <= ReorderPoint)
        $lowStockItems = Item::where('IsDeleted', false)
            ->whereRaw('StocksAvailable <= ReorderPoint')
            ->get();

        return view('dashboard.index', compact(
            'totalItems',
            'totalEmployees',
            'totalSuppliers',
            'lowStockItems'
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