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

    public function created_by_user()
    {
        return $this->belongsTo(UserAccount::class, 'CreatedById', 'UserAccountID')
                    ->from('UserAccount')  // Specify the exact table name
                    ->withDefault(['Username' => 'N/A']);
    }

    public function modified_by_user()
    {
        return $this->belongsTo(UserAccount::class, 'ModifiedById', 'UserAccountID')
                    ->from('UserAccount')  // Specify the exact table name
                    ->withDefault(['Username' => 'N/A']);
    }

    public function deleted_by_user()
    {
        return $this->belongsTo(UserAccount::class, 'DeletedById', 'UserAccountID')
                    ->from('UserAccount')  // Specify the exact table name
                    ->withDefault(['Username' => 'N/A']);
    }
} 