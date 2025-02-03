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

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'report_type' => 'required|in:inventory,items,low_stock'
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        switch ($validated['report_type']) {
            case 'inventory':
                $inventoryItems = Item::with(['classification'])
                    ->where('IsDeleted', 0)
                    ->get()
                    ->map(function ($item) {
                        $latestInventory = Inventory::where('ItemId', $item->ItemId)
                            ->where('IsDeleted', 0)
                            ->latest('DateCreated')
                            ->first();

                        return (object)[
                            'item' => $item,
                            'StocksAvailable' => $item->StocksAvailable ?? 0,
                            'StocksAdded' => $latestInventory->StocksAdded ?? 0,
                            'DateModified' => $latestInventory->DateModified ?? null
                        ];
                    });
                return view('reports.inventory', compact('inventoryItems', 'startDate', 'endDate'));

            case 'items':
                $data = Item::with(['classification'])
                    ->where('IsDeleted', false)
                    ->withCount(['inventories' => function($query) use ($startDate, $endDate) {
                        $query->whereBetween('DateCreated', [$startDate, $endDate])
                            ->where('IsDeleted', false);
                    }])
                    ->get();
                return view('reports.items', compact('data', 'startDate', 'endDate'));

            case 'low_stock':
                $data = Item::where('IsDeleted', false)
                    ->whereRaw('StocksAvailable <= ReorderPoint')
                    ->get();
                return view('reports.low_stock', compact('data', 'startDate', 'endDate'));
        }
    }

    public function generateInventoryReport(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->end_date) : Carbon::now();

        $inventoryItems = Item::with(['classification'])
            ->where('IsDeleted', 0)
            ->get()
            ->map(function ($item) {
                $latestInventory = Inventory::where('ItemId', $item->ItemId)
                    ->where('IsDeleted', 0)
                    ->latest('DateCreated')
                    ->first();

                return (object)[
                    'item' => $item,
                    'StocksAvailable' => $item->StocksAvailable ?? 0,
                    'StocksAdded' => $latestInventory->StocksAdded ?? 0,
                    'DateModified' => $latestInventory->DateModified ?? null
                ];
            });

        return view('reports.inventory', compact('inventoryItems', 'startDate', 'endDate'));
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
            ->whereRaw('StocksAvailable <= 10')
            ->get();

        return view('reports.low_stock', compact('lowStockItems'));
    }
}
