<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashDeposit;

class DepositController extends Controller
{
    public function index()
    {
        $deposits = CashDeposit::with('student')
                              ->orderBy('created_at', 'desc')
                              ->paginate(15);
        return view('pos.deposits.index', compact('deposits'));
    }

    public function store(Request $request)
    {
        // Validate and store deposit logic
    }

    public function destroy(CashDeposit $deposit)
    {
        // Soft delete deposit logic
    }
} 