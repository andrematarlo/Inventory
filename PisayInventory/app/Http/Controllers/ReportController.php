<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\POS;
use App\Models\Item;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;

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
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'report_type' => 'nullable|in:all,in,out'
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $reportType = $request->get('report_type', 'all');

        // Get inventory movements
        $query = Inventory::with([
            'item.classification',
            'createdByUser',
            'modifiedByUser',
            'deletedByUser'
        ])
            ->whereBetween('DateCreated', [$startDate, $endDate])
            ->where('IsDeleted', 0);

        // Filter by movement type
        if ($reportType === 'in') {
            $query->where('StocksAdded', '>', 0);
        } elseif ($reportType === 'out') {
            $query->where('StocksAdded', '<', 0);
        }

        $movements = $query->orderBy('DateCreated', 'desc')->get();

        // Get summary statistics
        $summary = [
            'total_items' => $movements->count(),
            'total_in' => $movements->where('StocksAdded', '>', 0)->sum('StocksAdded'),
            'total_out' => abs($movements->where('StocksAdded', '<', 0)->sum('StocksAdded')),
            'unique_items' => $movements->unique('ItemId')->count(),
        ];

        // Get current stock levels
        $currentStock = Item::with(['classification'])
            ->where('IsDeleted', 0)
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                $stockIn = Inventory::where('ItemId', $item->ItemId)
                    ->where('IsDeleted', 0)
                    ->where('StocksAdded', '>', 0)
                    ->whereBetween('DateCreated', [$startDate, $endDate])
                    ->sum('StocksAdded');

                $stockOut = abs(Inventory::where('ItemId', $item->ItemId)
                    ->where('IsDeleted', 0)
                    ->where('StocksAdded', '<', 0)
                    ->whereBetween('DateCreated', [$startDate, $endDate])
                    ->sum('StocksAdded'));

                return (object)[
                    'item' => $item,
                    'current_stock' => $item->StocksAvailable,
                    'stock_in' => $stockIn,
                    'stock_out' => $stockOut,
                    'net_movement' => $stockIn - $stockOut,
                    'reorder_point' => $item->ReorderPoint,
                    'needs_reorder' => $item->StocksAvailable <= $item->ReorderPoint
                ];
            });

        return view('reports.inventory', compact(
            'movements',
            'currentStock',
            'summary',
            'startDate',
            'endDate',
            'reportType'
        ));
    }

    public function generateInventoryPDF(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'report_type' => 'nullable|in:all,in,out'
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $reportType = $request->get('report_type', 'all');

        // Get inventory movements
        $query = Inventory::with([
            'item.classification',
            'createdByUser',
            'modifiedByUser',
            'deletedByUser'
        ])
            ->whereBetween('DateCreated', [$startDate, $endDate])
            ->where('IsDeleted', 0);

        // Filter by movement type
        if ($reportType === 'in') {
            $query->where('StocksAdded', '>', 0);
        } elseif ($reportType === 'out') {
            $query->where('StocksAdded', '<', 0);
        }

        $movements = $query->orderBy('DateCreated', 'desc')->get();

        // Get summary statistics
        $summary = [
            'total_items' => $movements->count(),
            'total_in' => $movements->where('StocksAdded', '>', 0)->sum('StocksAdded'),
            'total_out' => abs($movements->where('StocksAdded', '<', 0)->sum('StocksAdded')),
            'unique_items' => $movements->unique('ItemId')->count(),
        ];

        // Get current stock levels
        $currentStock = Item::with(['classification'])
            ->where('IsDeleted', 0)
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                $stockIn = Inventory::where('ItemId', $item->ItemId)
                    ->where('IsDeleted', 0)
                    ->where('StocksAdded', '>', 0)
                    ->whereBetween('DateCreated', [$startDate, $endDate])
                    ->sum('StocksAdded');

                $stockOut = abs(Inventory::where('ItemId', $item->ItemId)
                    ->where('IsDeleted', 0)
                    ->where('StocksAdded', '<', 0)
                    ->whereBetween('DateCreated', [$startDate, $endDate])
                    ->sum('StocksAdded'));

                return (object)[
                    'item' => $item,
                    'current_stock' => $item->StocksAvailable,
                    'stock_in' => $stockIn,
                    'stock_out' => $stockOut,
                    'net_movement' => $stockIn - $stockOut,
                    'reorder_point' => $item->ReorderPoint,
                    'needs_reorder' => $item->StocksAvailable <= $item->ReorderPoint
                ];
            });

        $pdf = PDF::loadView('reports.inventory-pdf', compact(
            'movements',
            'currentStock',
            'summary',
            'startDate',
            'endDate',
            'reportType'
        ));

        return $pdf->download('inventory-report.pdf');
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
