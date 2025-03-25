<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSTransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'item_id',
        'quantity',
        'unit_price',
        'subtotal',
        'discount',
        'notes'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2'
    ];

    /**
     * Get the transaction this item belongs to.
     */
    public function transaction()
    {
        return $this->belongsTo(POSTransaction::class, 'transaction_id');
    }

    /**
     * Get the inventory item.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
} 