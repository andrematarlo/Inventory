<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Generate inventory report.
     */
    public function inventory()
    {
        $items = Item::with(['supplier', 'classification', 'unit'])->get();
        return response()->json($items);
    }

    /**
     * Generate low stock report.
     */
    public function lowStock()
    {
        $lowStockItems = Item::where('quantity', '<', 10)
            ->with(['supplier', 'classification', 'unit'])
            ->get();
        
        return response()->json($lowStockItems);
    }

    /**
     * Generate sales report.
     */
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $query = Order::with(['student', 'orderItems.menuItem']);
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $sales = $query->get();
        
        return response()->json($sales);
    }
} 