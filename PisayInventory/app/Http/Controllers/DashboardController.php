<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Employee;
use App\Models\Supplier;
use App\Models\Inventory;
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
        try {
            $user = Auth::user();
            
            // Initialize all variables with default values
            $data = [
                'user' => $user,
                'totalItems' => 0,
                'totalEmployees' => 0,
                'totalSuppliers' => 0,
                'lowStockItems' => 0,
                'recentInventory' => collect([])  // Empty collection as default
            ];

            // Only try to get data if user is authenticated
            if (Auth::check()) {
                $data['totalItems'] = Item::where('IsDeleted', 0)->count();
                $data['totalEmployees'] = Employee::where('IsDeleted', 0)->count();
                $data['totalSuppliers'] = Supplier::where('IsDeleted', 0)->count();
                $data['lowStockItems'] = Item::where('IsDeleted', 0)
                    ->whereColumn('Quantity', '<=', 'ReorderPoint')
                    ->count();
                
                $data['recentInventory'] = Inventory::with(['item', 'employee'])
                    ->where('IsDeleted', 0)
                    ->orderBy('DateCreated', 'desc')
                    ->take(10)
                    ->get();

                Log::info('Dashboard data loaded successfully', $data);
            }

            return view('dashboard.index', $data);

        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            
            // Return view with error message and default values
            return view('dashboard.index', [
                'user' => Auth::user(),
                'error' => 'Error loading dashboard data',
                'totalItems' => 0,
                'totalEmployees' => 0,
                'totalSuppliers' => 0,
                'lowStockItems' => 0,
                'recentInventory' => collect([])
            ]);
        }
    }
} 