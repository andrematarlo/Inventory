<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    protected $table = 'purchase_order_detail';
    protected $primaryKey = 'PurchaseOrderDetailID';
    public $timestamps = false;

    protected $fillable = [
        'PurchaseOrderID',
        'ItemId',
        'Quantity',
        'UnitPrice',
        'TotalAmount'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemId', 'ItemId');
    }
} 