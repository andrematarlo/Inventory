<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Enums\PurchaseStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseService
{
    public function createPurchaseOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $purchase = Purchase::create([
                'PONumber' => $this->generatePONumber(),
                'SupplierID' => $data['SupplierID'],
                'OrderDate' => now(),
                'Status' => PurchaseStatus::PENDING->value,
                'CreatedById' => Auth::id(),
                'DateCreated' => now(),
            ]);

            foreach ($data['items'] as $item) {
                PurchaseItem::create([
                    'PurchaseOrderID' => $purchase->PurchaseOrderID,
                    'ItemId' => $item['ItemId'],
                    'Quantity' => $item['Quantity'],
                    'UnitPrice' => $item['UnitPrice'],
                    'CreatedById' => Auth::id(),
                    'DateCreated' => now(),
                ]);
            }

            return $purchase;
        });
    }

    private function generatePONumber()
    {
        $prefix = 'PO-' . date('Ym');
        $lastPO = Purchase::where('PONumber', 'like', $prefix . '%')
            ->orderBy('PONumber', 'desc')
            ->first();

        if (!$lastPO) {
            return $prefix . '-0001';
        }

        $lastNumber = intval(substr($lastPO->PONumber, -4));
        return $prefix . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
} 