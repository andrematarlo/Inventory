<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class POSOrderItem extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'pos_order_items';
    protected $primaryKey = 'OrderItemID';
    public $timestamps = false;
    
    protected $fillable = [
        'OrderID',
        'ItemID',
        'Quantity',
        'UnitPrice',
        'Subtotal',
        'ItemName',
        'IsCustomItem'
    ];
    
    protected $casts = [
        'IsCustomItem' => 'boolean',
        'UnitPrice' => 'decimal:2',
        'Subtotal' => 'decimal:2',
        'deleted_at' => 'datetime'
    ];
    
    /**
     * Get the order that this item belongs to.
     */
    public function order()
    {
        return $this->belongsTo(POSOrder::class, 'OrderID', 'OrderID');
    }
    
    /**
     * Get the item details.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemID', 'ItemId')
                    ->withDefault([
                        'ItemName' => $this->ItemName,
                        'Price' => $this->UnitPrice
                    ]);
    }

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($orderItem) {
            $orderItem->Subtotal = $orderItem->Quantity * $orderItem->UnitPrice;
        });
    }
} 