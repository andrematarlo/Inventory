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
        return $this->belongsTo(Supplier::class, 'SupplierID', 'SupplierID');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedById', 'UserAccountID')
                    ->from('UserAccount')
                    ->select(['UserAccountID', 'Username'])
                    ->withDefault(['Username' => 'N/A']);
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedById', 'UserAccountID')
                    ->from('UserAccount')
                    ->select(['UserAccountID', 'Username'])
                    ->withDefault(['Username' => 'N/A']);
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'DeletedById', 'UserAccountID')
                    ->from('UserAccount')
                    ->select(['UserAccountID', 'Username'])
                    ->withDefault(['Username' => 'N/A']);
    }

    public function restoredBy()
    {
        return $this->belongsTo(User::class, 'RestoredById', 'UserAccountID')
                    ->from('UserAccount')
                    ->select(['UserAccountID', 'Username'])
                    ->withDefault(['Username' => 'N/A']);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'ItemId', 'ItemId');
    }

    // Add this relationship
    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'CreatedById', 'UserAccountID');
    }

    // Add this relationship
    public function modified_by_user()
    {
        return $this->belongsTo(User::class, 'ModifiedById', 'UserAccountID');
    }

    // Add these relationships
    public function deleted_by_user()
    {
        return $this->belongsTo(User::class, 'DeletedById', 'UserAccountID');
    }

    public function restored_by_user()
    {
        return $this->belongsTo(User::class, 'RestoredById', 'UserAccountID');
    }
} 