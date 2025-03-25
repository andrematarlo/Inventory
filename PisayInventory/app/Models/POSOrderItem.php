<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class POSOrderItem extends Model
{
    protected $table = 'pos_order_items';
    
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
        'UnitPrice' => 'decimal:2',
        'Subtotal' => 'decimal:2',
        'IsCustomItem' => 'boolean'
    ];
    
    /**
     * Get the order that owns the item.
     */
    public function order()
    {
        return $this->belongsTo(POSOrder::class, 'OrderID', 'OrderID');
    }
    
    /**
     * Get the menu item.
     */
    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'ItemID', 'MenuItemID');
    }
} 