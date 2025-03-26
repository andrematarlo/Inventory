<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;  // Add this for deleted_at column

    protected $table = 'pos_order_items';
    protected $primaryKey = 'OrderItemID';
    
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
        'IsCustomItem' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'OrderID', 'OrderID');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'ItemID', 'MenuItemID');
    }
} 