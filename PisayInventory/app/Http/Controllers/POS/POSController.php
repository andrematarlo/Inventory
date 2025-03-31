<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\POSOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class POSController extends Controller
{
    public function store(Request $request)
    {
        // Create the order
        $order = POSOrder::create([
            'student_id' => $request->student_id,
            'TotalAmount' => $request->total_amount,
            'PaymentMethod' => $request->payment_type,
            'Status' => 'pending',
            'OrderNumber' => $orderNumber,
            'ProcessedBy' => Auth::id(),
            'ProcessedAt' => now(),
            'AmountTendered' => $request->payment_type === 'cash' ? $request->amount_tendered : null,
            'ChangeAmount' => $request->payment_type === 'cash' ? $request->change_amount : null
        ]);

        // Rest of the method remains unchanged
    }
} 