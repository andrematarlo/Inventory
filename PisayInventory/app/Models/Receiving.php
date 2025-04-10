<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receiving extends Model
{
    protected $table = 'receiving';
    protected $primaryKey = 'ReceivingID';
    public $timestamps = false;

    protected $fillable = [
        'PurchaseOrderID',
        'ReceivedByID',
        'DateReceived',
        'Status',
        'Notes',
        'DateCreated',
        'CreatedByID',
        'ModifiedByID',
        'DateModified',
        'DeletedByID',
        'DateDeleted',
        'RestoredById',
        'DateRestored',
        'IsDeleted',
        'ItemStatuses'
    ];

    protected $casts = [
        'DateReceived' => 'datetime',
        'DateCreated' => 'datetime',
        'DateModified' => 'datetime',
        'DateDeleted' => 'datetime',
        'DateRestored' => 'datetime',
        'IsDeleted' => 'boolean'
    ];

    // Add constants for status values
    const STATUS_RECEIVED = 'Received';
    const STATUS_PARTIAL = 'Partial';
    const STATUS_PENDING = 'Pending';

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'PurchaseOrderID');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'CreatedByID');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(Employee::class, 'ModifiedByID');
    }

    public function deletedBy()
    {
        return $this->belongsTo(Employee::class, 'DeletedByID');
    }

    public function restoredBy()
    {
        return $this->belongsTo(Employee::class, 'RestoredById');
    }

    public function receivingItems()
    {
        return $this->hasMany(ReceivingItem::class, 'ReceivingID', 'ReceivingID');
    }

    public function getTotalAmountAttribute()
    {
        if (!$this->items) {
            return 0;
        }
        
        return $this->items->sum(function($item) {
            return $item->Quantity * $item->UnitPrice;
        });
    }

    // Custom delete method to handle soft deletes with additional fields
    public function softDelete($deletedById)
    {
        $this->IsDeleted = true;
        $this->DeletedByID = $deletedById;
        $this->DateDeleted = now();
        return $this->save();
    }

    // Custom restore method
    public function softRestore($restoredById)
    {
        $this->IsDeleted = false;
        $this->RestoredById = $restoredById;
        $this->DateRestored = now();
        $this->DeletedByID = null;
        $this->DateDeleted = null;
        return $this->save();
    }

    public function receivedBy()
    {
        return $this->belongsTo(Employee::class, 'ReceivedByID', 'EmployeeID');
    }
} 