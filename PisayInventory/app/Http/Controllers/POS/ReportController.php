<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\CashDeposit;

class ReportController extends Controller
{
    public function index()
    {
        return view('pos.reports.index');
    }

    public function sales(Request $request)
    {
        $sales = Order::when($request->date_from, function($query) use ($request) {
            return $query->whereDate('created_at', '>=', $request->date_from);
        })
        ->when($request->date_to, function($query) use ($request) {
            return $query->whereDate('created_at', '<=', $request->date_to);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        return view('pos.reports.sales', compact('sales'));
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
} 