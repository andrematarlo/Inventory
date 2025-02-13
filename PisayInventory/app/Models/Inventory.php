<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'InventoryId';
    public $incrementing = true;
    public $timestamps = false;

    protected $attributes = [
        'StocksAdded' => 0,
        'StocksAvailable' => 0,
        'StockOut' => 0,
        'IsDeleted' => 0
    ];

    protected $fillable = [
        'InventoryId',
        'ItemId',
        'Quantity',
        'Unit',
        'ClassificationId',
        'Type',
        'StocksAdded',
        'StocksAvailable',
        'StockOut',
        'DateCreated',
        'CreatedById',
        'DateModified',
        'ModifiedById',
        'DateDeleted',
        'DeletedById',
        'DateRestored',
        'RestoredById',
        'IsDeleted'
    ];

    // Query scopes
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

    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemId', 'ItemId');
    }

    public function created_by_user()
    {
        return $this->belongsTo(UserAccount::class, 'CreatedById', 'UserAccountID');
    }

    public function modified_by_user()
    {
        return $this->belongsTo(UserAccount::class, 'ModifiedById', 'UserAccountID');
    }

    public function deleted_by_user()
    {
        return $this->belongsTo(User::class, 'DeletedById', 'UserAccountID')
                    ->withDefault(['Username' => 'N/A']);
    }
    public function restored_by_user()
    {
        return $this->belongsTo(User::class, 'RestoredById', 'UserAccountID')
                    
                    ->withDefault(['Username' => 'N/A']);
    }
} 