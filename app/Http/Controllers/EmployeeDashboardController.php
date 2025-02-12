<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Inventory;
use App\Models\Supplier;
use App\Models\Classification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeDashboardController extends Controller
{
    public function index()
    {
        // First, let's verify we're getting data
        $totalItems = DB::table('Items')
            ->where('IsDeleted', false)
            ->count();

        $totalSuppliers = DB::table('Suppliers')
            ->where('IsDeleted', false)
            ->count();

        $lowStockItems = DB::table('Inventory')
            ->where('IsDeleted', false)
            ->where('StocksAvailable', '<=', 10)
            ->where('StocksAvailable', '>', 0)
            ->count();

        $outOfStockItems = DB::table('Inventory')
            ->where('IsDeleted', false)
            ->where('StocksAvailable', 0)
            ->count();

        $itemsOverview = DB::table('Inventory')
            ->join('Items', 'Inventory.ItemId', '=', 'Items.ItemId')
            ->join('Classification', 'Inventory.ClassificationId', '=', 'Classification.ClassificationId')
            ->where('Inventory.IsDeleted', false)
            ->where('Items.IsDeleted', false)
            ->select(
                'Items.ItemName',
                'Classification.ClassificationName',
                'Inventory.StocksAvailable',
                DB::raw("CASE 
                    WHEN Inventory.StocksAvailable = 0 THEN 'Out of Stock'
                    WHEN Inventory.StocksAvailable <= 10 THEN 'Low Stock'
                    ELSE 'In Stock'
                END as status"),
                DB::raw("CASE 
                    WHEN Inventory.StocksAvailable = 0 THEN 'text-danger'
                    WHEN Inventory.StocksAvailable <= 10 THEN 'text-warning'
                    ELSE 'text-success'
                END as status_class")
            )
            ->get();

        // Let's dd() to see what data we're getting
        // dd($totalItems, $totalSuppliers, $lowStockItems, $outOfStockItems, $itemsOverview);

        return view('employee-dashboard', [
            'totalItems' => $totalItems,
            'totalSuppliers' => $totalSuppliers,
            'lowStockItems' => $lowStockItems,
            'outOfStockItems' => $outOfStockItems,
            'itemsOverview' => $itemsOverview
        ]);
    }
}