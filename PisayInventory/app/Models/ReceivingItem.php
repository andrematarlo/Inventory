<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivingItem extends Model
{
    protected $table = 'receiving_items';
    protected $primaryKey = 'ReceivingItemID';
    public $timestamps = false;

    protected $fillable = [
        'ReceivingID',
        'ItemId',
        'Quantity',
        'UnitPrice',
        'IsDeleted'
    ];

    public function receiving()
    {
        return $this->belongsTo(Receiving::class, 'ReceivingID', 'ReceivingID');
    }
} 