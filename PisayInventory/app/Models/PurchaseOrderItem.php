<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $table = 'purchase_order_items';
    protected $primaryKey = 'PurchaseOrderItemID';
    public $timestamps = false;

    protected $fillable = [
        'PurchaseOrderID',
        'ItemId',
        'Quantity',
        'UnitPrice',
        'TotalPrice',
        'DateCreated',
        'CreatedByID',
        'ModifiedByID',
        'DateModified',
        'DeletedByID',
        'DateDeleted',
        'RestoredById',
        'DateRestored',
        'IsDeleted'
    ];

    protected $casts = [
        'DateCreated' => 'datetime',
        'DateModified' => 'datetime',
        'DateDeleted' => 'datetime',
        'DateRestored' => 'datetime',
        'IsDeleted' => 'boolean',
        'UnitPrice' => 'decimal:2',
        'TotalPrice' => 'decimal:2'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'PurchaseOrderID', 'PurchaseOrderID');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemId', 'ItemId');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'CreatedByID', 'EmployeeID');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(Employee::class, 'ModifiedByID', 'EmployeeID');
    }
} 