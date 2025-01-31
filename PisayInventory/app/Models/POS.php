<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class POS extends Model
{
    protected $table = 'POS';
    protected $primaryKey = 'PurchaseId';
    public $timestamps = false;

    protected $fillable = [
        'ItemId',
        'UnitOfMeasureId',
        'ClassificationId',
        'Quantity',
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

    public function unitOfMeasure()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UnitOfMeasureId');
    }

    public function classification()
    {
        return $this->belongsTo(Classification::class, 'ClassificationId');
    }
} 