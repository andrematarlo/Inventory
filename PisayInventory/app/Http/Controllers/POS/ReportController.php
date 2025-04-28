<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\CashDeposit;
use App\Models\Item;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('pos.reports.index');
    }

    public function sales(Request $request)
    {
        // Get date range parameters
        $dateRange = $request->date_range ?? 'today';
        $startDate = null;
        $endDate = null;

        // Set date range based on selection
        switch ($dateRange) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'yesterday':
                $startDate = Carbon::yesterday();
                $endDate = Carbon::yesterday()->endOfDay();
                break;
            case 'last7days':
                $startDate = Carbon::now()->subDays(7)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'last30days':
                $startDate = Carbon::now()->subDays(30)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'custom':
                $startDate = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
                $endDate = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
                break;
        }

        // Get all items for the dropdown
        $items = Item::orderBy('ItemName')->get();

        // Get all cashiers for the dropdown
        $cashiers = User::where('role', 'cashier')->orderBy('name')->get();

        // Base query for sales data using a join to get order items
        $salesQuery = DB::table('pos_orders')
            ->join('pos_order_items', 'pos_orders.OrderID', '=', 'pos_order_items.OrderID')
            ->leftJoin('students', 'pos_orders.student_id', '=', 'students.student_id')
            ->select(
                'pos_orders.OrderID',
                'pos_orders.created_at',
                'pos_orders.PaymentMethod',
                'pos_orders.Status',
                'pos_orders.student_id as StudentID',
                'pos_order_items.ItemName',
                'pos_order_items.Quantity',
                'pos_order_items.UnitPrice',
                'pos_order_items.Subtotal'
            );

        // Apply date filters if dates are set
        if ($startDate) {
            $salesQuery->whereDate('pos_orders.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $salesQuery->whereDate('pos_orders.created_at', '<=', $endDate);
        }

        // Apply item filter if provided
        if ($request->item_id) {
            $salesQuery->where('pos_order_items.ItemID', $request->item_id);
        }

        // Apply cashier filter if provided
        if ($request->cashier_id) {
            $salesQuery->where('pos_orders.ProcessedBy', $request->cashier_id);
        }

        // Get paginated sales data
        $sales = $salesQuery->orderBy('pos_orders.created_at', 'desc')->paginate(15);

        // Calculate totals for summary cards
        $totals = $this->calculateSalesTotals($salesQuery);

        // Get top selling items
        $topItems = $this->getTopSellingItems($startDate, $endDate, $request->item_id);

        // Get chart data
        $chartData = $this->getChartData($startDate, $endDate, $request->item_id);

        return view('pos.reports.sales', compact(
            'sales', 
            'items', 
            'cashiers', 
            'dateRange', 
            'startDate', 
            'endDate', 
            'totals', 
            'topItems',
            'chartData'
        ));
    }

    public function deposits(Request $request)
    {
        $deposits = CashDeposit::with('student')
            ->when($request->date_from, function($query) use ($request) {
                return $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->date_to, function($query) use ($request) {
                return $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('pos.reports.deposits', compact('deposits'));
    }

    public function export(Request $request)
    {
        // Export reports logic
    }

    /**
     * Calculate sales totals for summary cards
     */
    private function calculateSalesTotals($query)
    {
        $clone = clone $query;
        
        $totalSales = $clone->sum('Subtotal');
        $totalOrders = $clone->distinct('OrderID')->count('OrderID');
        $totalItems = $clone->sum('Quantity');
        
        return (object)[
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'total_items' => $totalItems
        ];
    }

    /**
     * Get top selling items
     */
    private function getTopSellingItems($startDate, $endDate, $itemId = null)
    {
        $query = DB::table('pos_order_items')
            ->join('pos_orders', 'pos_order_items.OrderID', '=', 'pos_orders.OrderID')
            ->select(
                'pos_order_items.ItemName',
                DB::raw('SUM(pos_order_items.Quantity) as total_quantity'),
                DB::raw('SUM(pos_order_items.Subtotal) as total_revenue')
            )
            ->groupBy('pos_order_items.ItemName')
            ->orderBy('total_quantity', 'desc')
            ->limit(10);

        if ($startDate) {
            $query->whereDate('pos_orders.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('pos_orders.created_at', '<=', $endDate);
        }
        if ($itemId) {
            $query->where('pos_order_items.ItemID', $itemId);
        }

        return $query->get();
    }

    /**
     * Get chart data for sales trends
     */
    private function getChartData($startDate, $endDate, $itemId = null)
    {
        // Daily data
        $dailyQuery = DB::table('pos_orders')
            ->join('pos_order_items', 'pos_orders.OrderID', '=', 'pos_order_items.OrderID')
            ->select(
                DB::raw('DATE(pos_orders.created_at) as date'),
                DB::raw('SUM(pos_order_items.Subtotal) as total')
            )
            ->groupBy('date')
            ->orderBy('date');

        // Weekly data
        $weeklyQuery = DB::table('pos_orders')
            ->join('pos_order_items', 'pos_orders.OrderID', '=', 'pos_order_items.OrderID')
            ->select(
                DB::raw('YEARWEEK(pos_orders.created_at) as week'),
                DB::raw('SUM(pos_order_items.Subtotal) as total')
            )
            ->groupBy('week')
            ->orderBy('week');

        // Monthly data
        $monthlyQuery = DB::table('pos_orders')
            ->join('pos_order_items', 'pos_orders.OrderID', '=', 'pos_order_items.OrderID')
            ->select(
                DB::raw('DATE_FORMAT(pos_orders.created_at, "%Y-%m") as month'),
                DB::raw('SUM(pos_order_items.Subtotal) as total')
            )
            ->groupBy('month')
            ->orderBy('month');

        // Apply date filters
        if ($startDate) {
            $dailyQuery->whereDate('pos_orders.created_at', '>=', $startDate);
            $weeklyQuery->whereDate('pos_orders.created_at', '>=', $startDate);
            $monthlyQuery->whereDate('pos_orders.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $dailyQuery->whereDate('pos_orders.created_at', '<=', $endDate);
            $weeklyQuery->whereDate('pos_orders.created_at', '<=', $endDate);
            $monthlyQuery->whereDate('pos_orders.created_at', '<=', $endDate);
        }

        // Apply item filter if provided
        if ($itemId) {
            $dailyQuery->where('pos_order_items.ItemID', $itemId);
            $weeklyQuery->where('pos_order_items.ItemID', $itemId);
            $monthlyQuery->where('pos_order_items.ItemID', $itemId);
        }

        // Format data for charts
        $dailyData = $dailyQuery->get();
        $weeklyData = $weeklyQuery->get();
        $monthlyData = $monthlyQuery->get();

        return [
            'daily' => [
                'labels' => $dailyData->pluck('date'),
                'data' => $dailyData->pluck('total')
            ],
            'weekly' => [
                'labels' => $weeklyData->pluck('week'),
                'data' => $weeklyData->pluck('total')
            ],
            'monthly' => [
                'labels' => $monthlyData->pluck('month'),
                'data' => $monthlyData->pluck('total')
            ]
        ];
    }
} 