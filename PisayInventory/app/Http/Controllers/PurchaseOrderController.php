<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function restore($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);
            $purchaseOrder->update([
                'IsDeleted' => false,
                'RestoredById' => auth()->user()->UserAccountID,
                'DateRestored' => now(),
                'DeletedById' => null,
                'DateDeleted' => null
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $userPermissions = $this->getUserPermissions();
            
            if (!$userPermissions || !$userPermissions->CanAdd) {
                return redirect()->back()->with('error', 'You do not have permission to add purchase orders.');
            }

            $request->validate([
                'items' => 'required|array',
                'items.*.ItemId' => 'required|exists:items,ItemId',
                'items.*.Quantity' => 'required|integer|min:1',
                'items.*.UnitPrice' => 'required|numeric|min:0|max:999999999.99',
                'DeliveryDate' => 'required|date'
            ], [
                'items.*.UnitPrice.max' => 'The unit price must not be greater than 999,999,999.99'
            ]);

            // Add your store logic here
            
        } catch (\Exception $e) {
            \Log::error('Error creating purchase order: ' . $e->getMessage());
            return back()->with('error', 'Error creating purchase order: ' . $e->getMessage());
        }
    }
} 