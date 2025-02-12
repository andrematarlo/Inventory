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
        'ImagePath',
        'UnitOfMeasureId',
        'ClassificationId',
        'SupplierID',
        'StocksAvailable',
        'ReorderPoint',
        'CreatedById',
        'DateCreated',
        'ModifiedById',
        'DateModified',
        'DeletedById',
        'DateDeleted',
        'RestoredById',
        'DateRestored',
        'IsDeleted'
    ];

    // Add query scopes
    public function scopeActive($query)
    {
        return $query->where('IsDeleted', false);
    }

    public function scopeTrashed($query)
    {
        return $query->where('IsDeleted', true);
    }

    // Relationships
    public function classification()
    {
        return $this->belongsTo(Classification::class, 'ClassificationId', 'ClassificationId');
    }

    public function unitOfMeasure()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UnitOfMeasureId', 'UnitOfMeasureId')
                    ->withDefault(['UnitName' => 'N/A']);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierID', 'SupplierID')
                    ->withDefault(['SupplierName' => 'N/A']);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedById', 'UserAccountID');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedById', 'UserAccountID');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'DeletedById', 'UserAccountID');
    }

    public function restoredBy()
    {
        return $this->belongsTo(User::class, 'RestoredById', 'UserAccountID');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'ItemId', 'ItemId');
    }
} 