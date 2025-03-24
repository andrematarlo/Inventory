<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MenuItem;
use App\Models\Order;
use Auth;

class KioskController extends Controller
{
    public function index()
    {
        $menuItems = MenuItem::where('IsDeleted', 0)->get();
        $categories = Classification::where('IsDeleted', 0)->get();
        $studentBalance = Auth::user()->getBalance(); // Implement this method in User model
        
        return view('pos.kiosk.index', compact('menuItems', 'categories', 'studentBalance'));
    }

    public function store(Request $request)
    {
        // Validate and store order logic
    }

    public function history()
    {
        $orders = Order::where('student_id', Auth::user()->student_id)
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);
                      
        return view('pos.kiosk.history', compact('orders'));
    }
} 