<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';
    protected $primaryKey = 'ItemId';
    public $timestamps = false;

    protected $fillable = [
        'ItemName',
        'Description',
        'ClassificationId',
        'UnitOfMeasureId',
        'SupplierID',
        'StocksAvailable',
        'ReorderPoint',
        'CreatedById',
        'DateCreated',
        'ModifiedById',
        'DateModified',
        'DeletedById',
        'DateDeleted',
        'IsDeleted'
    ];

    public function classification()
    {
        return $this->belongsTo(Classification::class, 'ClassificationId', 'ClassificationId')
                    ->where('IsDeleted', 0)
                    ->withDefault(['ClassificationName' => 'N/A']);
    }

    public function unitOfMeasure()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UnitOfMeasureId', 'UnitOfMeasureId')
                    ->where('IsDeleted', 0)
                    ->withDefault(['UnitName' => 'N/A']);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierID', 'SupplierID')
                    ->where('IsDeleted', 0)
                    ->withDefault(['SupplierName' => 'N/A']);
    }

    public function createdBy()
    {
        return $this->belongsTo(UserAccount::class, 'CreatedById', 'UserAccountID')
                    ->withDefault(['Username' => 'N/A']);
    }

    public function modifiedBy()
    {
        return $this->belongsTo(UserAccount::class, 'ModifiedById', 'UserAccountID')
                    ->withDefault(['Username' => 'N/A']);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'ItemId', 'ItemId');
    }
} 