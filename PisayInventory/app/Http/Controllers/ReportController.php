<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Purchase;
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
            'report_type' => 'required|in:inventory,items,low_stock,pos'
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

            case 'pos':
                $posData = Purchase::withTrashed()
                    ->with(['item', 'unit_of_measure', 'classification'])
                    ->whereBetween('DateCreated', [$startDate, $endDate])
                    ->where('IsDeleted', 0)
                    ->orderBy('DateCreated', 'desc')
                    ->get();

                $summary = [
                    'total_purchases' => $posData->count(),
                    'total_quantity' => $posData->sum('Quantity'),
                    'total_stocks_added' => $posData->sum('StocksAdded')
                ];

                return view('reports.pos', compact('posData', 'summary', 'startDate', 'endDate'));
        }
    }

    public function generateInventoryReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'report_type' => 'nullable|in:all,in,out'
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        $reportType = $request->input('report_type', 'all');

        $query = Inventory::with([
            'item.classification',
            'created_by_user',
            'modified_by_user'
        ])
            ->whereBetween('DateCreated', [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')])
            ->where('IsDeleted', 0);

        if ($reportType === 'in') {
            $query->where('StocksAdded', '>', 0);
        } elseif ($reportType === 'out') {
            $query->where('StocksAdded', '<', 0);
        }

        $movements = $query->orderBy('DateCreated', 'desc')->get();

        $summary = [
            'total_items' => $movements->count(),
            'total_in' => $movements->where('StocksAdded', '>', 0)->sum('StocksAdded'),
            'total_out' => abs($movements->where('StocksAdded', '<', 0)->sum('StocksAdded')),
            'unique_items' => $movements->unique('ItemId')->count()
        ];

        $currentStock = Item::with(['classification'])
            ->where('IsDeleted', 0)
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                $stockIn = Inventory::where('ItemId', $item->ItemId)
                    ->where('IsDeleted', 0)
                    ->where('StocksAdded', '>', 0)
                    ->whereBetween('DateCreated', [
                        $startDate->format('Y-m-d H:i:s'),
                        $endDate->format('Y-m-d H:i:s')
                    ])
                    ->sum('StocksAdded');

                $stockOut = abs(Inventory::where('ItemId', $item->ItemId)
                    ->where('IsDeleted', 0)
                    ->where('StocksAdded', '<', 0)
                    ->whereBetween('DateCreated', [
                        $startDate->format('Y-m-d H:i:s'),
                        $endDate->format('Y-m-d H:i:s')
                    ])
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

        return view('reports.inventory', compact('movements', 'summary', 'startDate', 'endDate', 'reportType', 'currentStock'));
    }

    public function generateInventoryPDF(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        $reportType = $request->input('report_type', 'all');

        $query = Inventory::with([
            'item.classification',
            'created_by_user',
            'modified_by_user'
        ])
        ->whereBetween('DateCreated', [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')])
        ->where('IsDeleted', 0);

        if ($reportType === 'in') {
            $query->where('StocksAdded', '>', 0);
        } elseif ($reportType === 'out') {
            $query->where('StocksAdded', '<', 0);
        }

        $movements = $query->orderBy('DateCreated', 'desc')->get();

        $summary = [
            'total_items' => $movements->count(),
            'total_in' => $movements->where('StocksAdded', '>', 0)->sum('StocksAdded'),
            'total_out' => abs($movements->where('StocksAdded', '<', 0)->sum('StocksAdded')),
            'unique_items' => $movements->unique('ItemId')->count()
        ];

        $currentStock = Item::with(['classification'])
            ->where('IsDeleted', 0)
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                $stockIn = Inventory::where('ItemId', $item->ItemId)
                    ->where('IsDeleted', 0)
                    ->where('StocksAdded', '>', 0)
                    ->whereBetween('DateCreated', [
                        $startDate->format('Y-m-d H:i:s'),
                        $endDate->format('Y-m-d H:i:s')
                    ])
                    ->sum('StocksAdded');

                $stockOut = abs(Inventory::where('ItemId', $item->ItemId)
                    ->where('IsDeleted', 0)
                    ->where('StocksAdded', '<', 0)
                    ->whereBetween('DateCreated', [
                        $startDate->format('Y-m-d H:i:s'),
                        $endDate->format('Y-m-d H:i:s')
                    ])
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

        $view = view('reports.inventory-pdf', compact(
            'movements',
            'currentStock',
            'summary',
            'startDate',
            'endDate',
            'reportType'
        ))->render();

        $pdf = PDF::loadHTML($view);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial'
        ]);

        return $pdf->download('inventory-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function generateSalesReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // Fetch sales data with more detailed analysis, including soft deleted records
        $salesData = Purchase::withTrashed()
            ->with(['item', 'unit_of_measure', 'classification'])
            ->whereBetween('DateCreated', [$startDate, $endDate])
            ->get();

        // Calculate sales summary
        $summary = [
            'total_sales_volume' => $salesData->sum('Quantity'),
            'total_unique_items' => $salesData->unique('ItemId')->count(),
            'total_sales_value' => $salesData->sum(function($sale) {
                // Assuming you want to calculate sales value
                return $sale->Quantity * ($sale->item->UnitPrice ?? 0);
            })
        ];

        // Group sales by item for detailed breakdown
        $salesByItem = $salesData->groupBy('ItemId')->map(function($group) {
            $firstItem = $group->first();
            return [
                'item_name' => $firstItem->item->ItemName ?? 'Unknown',
                'total_quantity' => $group->sum('Quantity'),
                'classification' => $firstItem->classification->ClassificationName ?? 'Uncategorized'
            ];
        });

        return view('reports.sales', compact('salesData', 'summary', 'salesByItem', 'startDate', 'endDate'));
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
