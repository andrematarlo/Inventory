<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\POS;
use App\Models\Item;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function generateInventoryReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $inventory = Inventory::with(['item', 'classification'])
            ->whereBetween('DateCreated', [
                $request->start_date,
                Carbon::parse($request->end_date)->endOfDay()
            ])
            ->where('IsDeleted', false)
            ->get();

        return view('reports.inventory', compact('inventory'));
    }

    public function generateSalesReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $sales = POS::with(['item', 'unitOfMeasure', 'classification'])
            ->whereBetween('DateCreated', [
                $request->start_date,
                Carbon::parse($request->end_date)->endOfDay()
            ])
            ->where('IsDeleted', false)
            ->get();

        return view('reports.sales', compact('sales'));
    }

    public function generateLowStockReport()
    {
        $lowStockItems = Inventory::with(['item', 'classification'])
            ->where('IsDeleted', false)
            ->whereRaw('StocksAvailable <= 10') // You can adjust the threshold
            ->get();

        return view('reports.low_stock', compact('lowStockItems'));
    }
}
