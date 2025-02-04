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

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'CreatedById', 'UserAccountID')
                    ->from('UserAccount')
                    ->withDefault(['Username' => 'N/A']);
    }

    public function modified_by_user()
    {
        return $this->belongsTo(User::class, 'ModifiedById', 'UserAccountID')
                    ->from('UserAccount')
                    ->withDefault(['Username' => 'N/A']);
    }

    public function deleted_by_user()
    {
        return $this->belongsTo(User::class, 'DeletedById', 'UserAccountID')
                    ->from('UserAccount')
                    ->withDefault(['Username' => 'N/A']);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'ItemId', 'ItemId');
    }
} 