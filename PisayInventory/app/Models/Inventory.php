<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'Inventory';
    public $timestamps = false;

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'ItemId',
        'ClassificationId',
        'StocksAvailable',
        'StocksAdded',
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
        return $this->belongsTo(Item::class, 'ItemId');
    }

    public function classification()
    {
        return $this->belongsTo(Classification::class, 'ClassificationId');
    }
} 