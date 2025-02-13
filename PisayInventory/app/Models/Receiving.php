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
        'IsDeleted'
    ];

    protected $casts = [
        'DateReceived' => 'datetime',
        'DateCreated' => 'datetime',
        'DateModified' => 'datetime',
        'DateDeleted' => 'datetime',
        'DateRestored' => 'datetime',
        'IsDeleted' => 'boolean'
    ];

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
} 