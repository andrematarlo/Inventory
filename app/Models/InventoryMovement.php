<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $table = 'inventory_movement';
    protected $primaryKey = 'MovementID';
    public $timestamps = false;

    protected $fillable = [
        'ItemID',
        'MovementType',
        'Quantity',
        'ReferenceNumber',
        'ReferenceType',
        'ReferenceID',
        'Notes',
        'DateCreated',
        'CreatedByID',
        'DateModified',
        'ModifiedByID',
        'IsDeleted',
        'DateDeleted',
        'DeletedByID'
    ];

    protected $casts = [
        'DateCreated' => 'datetime',
        'DateModified' => 'datetime',
        'DateDeleted' => 'datetime',
        'IsDeleted' => 'boolean'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemID', 'ItemID');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'CreatedByID', 'EmployeeID');
    }
} 