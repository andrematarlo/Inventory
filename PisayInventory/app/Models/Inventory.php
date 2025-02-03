<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'InventoryId';
    public $timestamps = false;

    protected $fillable = [
        'ItemId',
        'Quantity',
        'EmployeeId',
        'DateCreated',
        'CreatedById',
        'DateModified',
        'ModifiedById',
        'DateDeleted',
        'DeletedById',
        'IsDeleted'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemId', 'ItemId');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeId', 'EmployeeId');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedById', 'UserAccountId');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedById', 'UserAccountId');
    }
} 