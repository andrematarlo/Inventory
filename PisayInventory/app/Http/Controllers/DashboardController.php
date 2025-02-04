<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Classification;
use App\Models\Supplier;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            // Get statistics
            $totalItems = Item::where('IsDeleted', false)->count();
            $lowStockItems = Item::where('IsDeleted', false)
                ->whereColumn('StocksAvailable', '<=', 'ReorderPoint')
                ->count();
            $totalSuppliers = Supplier::where('IsDeleted', false)->count();
            $totalClassifications = Classification::where('IsDeleted', false)->count();

            // Get low stock items list
            $lowStockItemsList = Item::where('IsDeleted', false)
                ->whereColumn('StocksAvailable', '<=', 'ReorderPoint')
                ->select('ItemName', 'StocksAvailable')
                ->get();

            // Get recent activities
            $recentActivities = Inventory::with(['item', 'user'])
                ->where('IsDeleted', false)
                ->orderBy('DateCreated', 'desc')
                ->take(10)
                ->get()
                ->map(function ($activity) {
                    return [
                        'item_name' => $activity->item->ItemName ?? 'Unknown Item',
                        'action' => $this->getActionType($activity->Type),
                        'action_color' => $this->getActionColor($activity->Type),
                        'user_name' => $activity->user->Username ?? 'Unknown User',
                        'date' => date('M d, Y h:i A', strtotime($activity->DateCreated))
                    ];
                });

            return view('dashboard', compact(
                'totalItems',
                'lowStockItems',
                'totalSuppliers',
                'totalClassifications',
                'lowStockItemsList',
                'recentActivities'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading dashboard: ' . $e->getMessage());
            return view('dashboard')->with('error', 'Error loading dashboard data');
        }
    }

    private function getActionType($type)
    {
        return match ($type) {
            'IN' => 'Stock In',
            'OUT' => 'Stock Out',
            default => 'Unknown'
        };
    }

    private function getActionColor($type)
    {
        return match ($type) {
            'IN' => 'success',
            'OUT' => 'danger',
            default => 'secondary'
        };
    }
}