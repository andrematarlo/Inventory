<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Inventory;
use App\Models\Supplier;
use App\Models\Classification;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $totalItems = Item::count();
        $lowStockItems = Inventory::where('StocksAvailable', '<=', 10)->count();
        $totalSuppliers = Supplier::count();
        $totalClassifications = Classification::count();

        // Get low stock items list
        $lowStockItemsList = Inventory::with('item')
            ->where('StocksAvailable', '<=', 10)
            ->get();

        // Get real recent activities from your database
        $recentActivities = collect([]); // Empty collection for now until we implement activity logging

        return view('dashboard', compact(
            'totalItems',
            'lowStockItems',
            'totalSuppliers',
            'totalClassifications',
            'lowStockItemsList',
            'recentActivities'
        ));
    }
} 