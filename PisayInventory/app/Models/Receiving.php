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
        return $this->belongsTo(PurchaseOrder::class, 'PurchaseOrderID', 'PurchaseOrderID');
    }

    public function receivedBy()
    {
        return $this->belongsTo(Employee::class, 'ReceivedByID', 'EmployeeID');
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
} 