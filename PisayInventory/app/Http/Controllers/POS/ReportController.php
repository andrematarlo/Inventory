<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\POSOrder;
use App\Models\POSOrderItem;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Excel;
use PDF;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cashiers = User::whereHas('roles', function($query) {
            $query->where('name', 'Cashier');
        })->get();
        
        return view('pos.reports.index', compact('cashiers'));
    }
    
    /**
     * Generate sales report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function salesReport(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'report_type' => 'required|in:daily,weekly,monthly,annual',
        ]);
        
        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();
        
        $query = POSOrder::with(['items.item', 'processedBy'])
            ->where('Status', 'Completed')
            ->whereBetween('CompletedDate', [$dateFrom, $dateTo]);
            
        // Format results based on report type
        switch ($request->report_type) {
            case 'daily':
                $results = $query->get()
                    ->groupBy(function($order) {
                        return Carbon::parse($order->CompletedDate)->format('Y-m-d');
                    });
                break;
                
            case 'weekly':
                $results = $query->get()
                    ->groupBy(function($order) {
                        $date = Carbon::parse($order->CompletedDate);
                        return $date->year . '-W' . $date->week;
                    });
                break;
                
            case 'monthly':
                $results = $query->get()
                    ->groupBy(function($order) {
                        return Carbon::parse($order->CompletedDate)->format('Y-m');
                    });
                break;
                
            case 'annual':
                $results = $query->get()
                    ->groupBy(function($order) {
                        return Carbon::parse($order->CompletedDate)->format('Y');
                    });
                break;
        }
        
        // Calculate totals
        $totalSales = $query->sum('TotalAmount');
        $orderCount = $query->count();
        
        return view('pos.reports.sales', compact(
            'results', 
            'totalSales', 
            'orderCount', 
            'dateFrom', 
            'dateTo', 
            'request'
        ));
    }
    
    /**
     * Generate sales by cashier report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function salesByCashier(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'cashier_id' => 'nullable|exists:users,id',
            'report_type' => 'required|in:daily,weekly,monthly,annual',
        ]);
        
        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();
        
        $query = POSOrder::with(['items.item', 'processedBy'])
            ->where('Status', 'Completed')
            ->whereBetween('CompletedDate', [$dateFrom, $dateTo]);
            
        // Filter by cashier if specified
        if ($request->cashier_id) {
            $query->where('ProcessedBy', $request->cashier_id);
            $cashier = User::find($request->cashier_id);
        }
        
        // Group by cashier first
        $results = $query->get()->groupBy('ProcessedBy');
        
        // Format results based on report type for each cashier
        $formattedResults = [];
        foreach ($results as $cashierId => $cashierOrders) {
            switch ($request->report_type) {
                case 'daily':
                    $formattedResults[$cashierId] = $cashierOrders
                        ->groupBy(function($order) {
                            return Carbon::parse($order->CompletedDate)->format('Y-m-d');
                        });
                    break;
                    
                case 'weekly':
                    $formattedResults[$cashierId] = $cashierOrders
                        ->groupBy(function($order) {
                            $date = Carbon::parse($order->CompletedDate);
                            return $date->year . '-W' . $date->week;
                        });
                    break;
                    
                case 'monthly':
                    $formattedResults[$cashierId] = $cashierOrders
                        ->groupBy(function($order) {
                            return Carbon::parse($order->CompletedDate)->format('Y-m');
                        });
                    break;
                    
                case 'annual':
                    $formattedResults[$cashierId] = $cashierOrders
                        ->groupBy(function($order) {
                            return Carbon::parse($order->CompletedDate)->format('Y');
                        });
                    break;
            }
        }
        
        // Calculate totals
        $totalSales = $query->sum('TotalAmount');
        $orderCount = $query->count();
        
        // Get all cashiers for the filter dropdown
        $cashiers = User::whereHas('roles', function($query) {
            $query->where('name', 'Cashier');
        })->get();
        
        return view('pos.reports.sales_by_cashier', compact(
            'formattedResults', 
            'totalSales', 
            'orderCount', 
            'dateFrom', 
            'dateTo', 
            'cashiers',
            'request'
        ));
    }
    
    /**
     * Generate sales by item report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function salesByItem(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'item_id' => 'nullable|exists:items,ItemID',
            'report_type' => 'required|in:daily,weekly,monthly,annual',
        ]);
        
        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();
        
        // Get completed orders between dates
        $orderQuery = POSOrder::where('Status', 'Completed')
            ->whereBetween('CompletedDate', [$dateFrom, $dateTo]);
            
        $orderIds = $orderQuery->pluck('OrderID');
        
        // Query order items
        $query = POSOrderItem::with(['order', 'item'])
            ->whereIn('OrderID', $orderIds);
            
        // Filter by item if specified
        if ($request->item_id) {
            $query->where('ItemID', $request->item_id);
            $selectedItem = Item::find($request->item_id);
        }
        
        // Get all items with their sales data
        $orderItems = $query->get();
        
        // Group by item
        $itemSales = $orderItems->groupBy('ItemID');
        
        // Format results based on report type for each item
        $formattedResults = [];
        foreach ($itemSales as $itemId => $items) {
            switch ($request->report_type) {
                case 'daily':
                    $formattedResults[$itemId] = $items
                        ->groupBy(function($item) {
                            return Carbon::parse($item->order->CompletedDate)->format('Y-m-d');
                        });
                    break;
                    
                case 'weekly':
                    $formattedResults[$itemId] = $items
                        ->groupBy(function($item) {
                            $date = Carbon::parse($item->order->CompletedDate);
                            return $date->year . '-W' . $date->week;
                        });
                    break;
                    
                case 'monthly':
                    $formattedResults[$itemId] = $items
                        ->groupBy(function($item) {
                            return Carbon::parse($item->order->CompletedDate)->format('Y-m');
                        });
                    break;
                    
                case 'annual':
                    $formattedResults[$itemId] = $items
                        ->groupBy(function($item) {
                            return Carbon::parse($item->order->CompletedDate)->format('Y');
                        });
                    break;
            }
        }
        
        // Calculate totals
        $totalQuantity = $orderItems->sum('Quantity');
        $totalSales = $orderItems->sum('Subtotal');
        
        // Get all items for the filter dropdown
        $items = Item::where('Status', 'Active')->orderBy('ItemName')->get();
        
        return view('pos.reports.sales_by_item', compact(
            'formattedResults', 
            'totalQuantity',
            'totalSales', 
            'dateFrom', 
            'dateTo', 
            'items',
            'request'
        ));
    }
    
    /**
     * Export report to PDF or Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exportReport(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:sales,sales_by_cashier,sales_by_item',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'format' => 'required|in:pdf,excel',
        ]);
        
        // Based on the report type, call the appropriate method
        switch ($request->report_type) {
            case 'sales':
                $data = $this->getSalesReportData($request);
                $fileName = 'sales_report_' . date('Y-m-d');
                break;
                
            case 'sales_by_cashier':
                $data = $this->getSalesByCashierReportData($request);
                $fileName = 'sales_by_cashier_report_' . date('Y-m-d');
                break;
                
            case 'sales_by_item':
                $data = $this->getSalesByItemReportData($request);
                $fileName = 'sales_by_item_report_' . date('Y-m-d');
                break;
        }
        
        // Export based on requested format
        if ($request->format === 'pdf') {
            $pdf = PDF::loadView('pos.reports.exports.' . $request->report_type . '_pdf', $data);
            return $pdf->download($fileName . '.pdf');
        } else {
            // Excel export would be implemented here
            // For now, we'll return a message
            return back()->with('info', 'Excel export is not yet implemented.');
        }
    }
    
    /**
     * Get data for sales report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function getSalesReportData(Request $request)
    {
        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();
        
        $orders = POSOrder::with(['items.item', 'processedBy'])
            ->where('Status', 'Completed')
            ->whereBetween('CompletedDate', [$dateFrom, $dateTo])
            ->get();
            
        return [
            'orders' => $orders,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalSales' => $orders->sum('TotalAmount'),
            'orderCount' => $orders->count(),
        ];
    }
    
    /**
     * Get data for sales by cashier report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function getSalesByCashierReportData(Request $request)
    {
        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();
        
        $query = POSOrder::with(['items.item', 'processedBy'])
            ->where('Status', 'Completed')
            ->whereBetween('CompletedDate', [$dateFrom, $dateTo]);
            
        if ($request->cashier_id) {
            $query->where('ProcessedBy', $request->cashier_id);
            $cashier = User::find($request->cashier_id);
        }
        
        $orders = $query->get();
        
        return [
            'orders' => $orders,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalSales' => $orders->sum('TotalAmount'),
            'orderCount' => $orders->count(),
            'cashier' => $cashier ?? null,
        ];
    }
    
    /**
     * Get data for sales by item report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function getSalesByItemReportData(Request $request)
    {
        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();
        
        $orderIds = POSOrder::where('Status', 'Completed')
            ->whereBetween('CompletedDate', [$dateFrom, $dateTo])
            ->pluck('OrderID');
            
        $query = POSOrderItem::with(['order', 'item'])
            ->whereIn('OrderID', $orderIds);
            
        if ($request->item_id) {
            $query->where('ItemID', $request->item_id);
            $item = Item::find($request->item_id);
        }
        
        $orderItems = $query->get();
        
        return [
            'orderItems' => $orderItems,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalQuantity' => $orderItems->sum('Quantity'),
            'totalSales' => $orderItems->sum('Subtotal'),
            'item' => $item ?? null,
        ];
    }
} 