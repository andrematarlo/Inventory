<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'InventoryId';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'ItemId',
        'ClassificationId',
        'StocksAdded',
        'StocksAvailable',
        'DateCreated',
        'CreatedById',
        'DateModified',
        'ModifiedById',
        'DateDeleted',
        'DeletedById',
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
    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemId', 'ItemId');
    }

    public function createdByUser()
    {
        return $this->belongsTo(UserAccount::class, 'CreatedById', 'UserAccountID')
                    ->withDefault(['Username' => 'N/A']);
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(UserAccount::class, 'ModifiedById', 'UserAccountID')
                    ->withDefault(['Username' => 'N/A']);
    }

    public function deletedByUser()
    {
        return $this->belongsTo(UserAccount::class, 'DeletedById', 'UserAccountID')
                    ->withDefault(['Username' => 'N/A']);
    }
} 