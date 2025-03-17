<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSOrderItem extends Model
{
    use HasFactory;
    
    protected $table = 'pos_order_items';
    protected $primaryKey = 'OrderItemID';
    public $timestamps = false;
    
    protected $fillable = [
        'OrderID',
        'ItemID',
        'Quantity',
        'UnitPrice',
        'Subtotal'
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
        return $this->belongsTo(Item::class, 'ItemID', 'ItemID');
    }
} 