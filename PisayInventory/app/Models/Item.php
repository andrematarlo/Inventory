<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'Items';
    protected $primaryKey = 'ItemId';
    public $timestamps = false;

    protected $fillable = [
        'ItemName',
        'Description',
        'UnitOfMeasureId',
        'ClassificationId',
        'SupplierID',
        'CreatedById',
        'DateCreated',
        'ModifiedById',
        'DateModified',
        'DeletedById',
        'DateDeleted',
        'IsDeleted'
    ];

    protected $attributes = [
        'IsDeleted' => false
    ];

    public function unitOfMeasure()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UnitOfMeasureId');
    }

    public function classification()
    {
        return $this->belongsTo(Classification::class, 'ClassificationId');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierID');
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'ItemId');
    }
} 