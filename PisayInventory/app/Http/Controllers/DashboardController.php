<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Inventory;
use App\Models\Supplier;
use App\Models\Classification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $user = Auth::user();
        \Log::info('Dashboard accessed', [
            'user_id' => $user->getAuthIdentifier(),
            'is_authenticated' => Auth::check()
        ]);

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
            'user',
            'totalItems',
            'lowStockItems',
            'totalSuppliers',
            'totalClassifications',
            'lowStockItemsList',
            'recentActivities'
        ));
    }
} 