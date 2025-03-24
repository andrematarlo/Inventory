<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\Classification;

class CashierController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at', 'desc')->paginate(15);
        return view('pos.cashier.index', compact('orders'));
    }

    public function create()
    {
        $menuItems = MenuItem::where('IsDeleted', 0)->get();
        $categories = Classification::where('IsDeleted', 0)->get();
        return view('pos.cashier.create', compact('menuItems', 'categories'));
    }

    public function store(Request $request)
    {
        // Validate and store order logic
    }

    public function show(Order $order)
    {
        return view('pos.cashier.show', compact('order'));
    }
} 