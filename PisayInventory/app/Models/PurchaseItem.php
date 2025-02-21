<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PurchaseItem extends Model
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
        'RestoredById',
        'DateDeleted',
        'DateRestored',
        'IsDeleted'
    ];

    protected $casts = [
        'Quantity' => 'integer',
        'UnitPrice' => 'decimal:2',
        'TotalPrice' => 'decimal:2',
        'IsDeleted' => 'boolean',
        'DateCreated' => 'datetime',
        'DateModified' => 'datetime',
        'DateDeleted' => 'datetime',
        'DateRestored' => 'datetime'
    ];

    // Relationships
    public function purchaseOrder()
    {
        return $this->belongsTo(Purchase::class, 'PurchaseOrderID', 'PurchaseOrderID');
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

    public function deletedBy()
    {
        return $this->belongsTo(Employee::class, 'DeletedByID', 'EmployeeID');
    }

    public function restoredBy()
    {
        return $this->belongsTo(Employee::class, 'RestoredById', 'EmployeeID');
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->CreatedByID = Auth::id();
            $item->DateCreated = now();
            // Calculate TotalPrice
            $item->TotalPrice = $item->Quantity * $item->UnitPrice;
        });

        static::updating(function ($item) {
            $item->ModifiedByID = Auth::id();
            $item->DateModified = now();
            // Recalculate TotalPrice
            $item->TotalPrice = $item->Quantity * $item->UnitPrice;
        });
    }

    // Soft delete functionality
    public function softDelete()
    {
        $this->IsDeleted = true;
        $this->DeletedByID = Auth::id();
        $this->DateDeleted = now();
        return $this->save();
    }

    public function restore()
    {
        $this->IsDeleted = false;
        $this->DeletedByID = null;
        $this->DateDeleted = null;
        $this->RestoredById = Auth::id();
        $this->DateRestored = now();
        return $this->save();
    }
} 