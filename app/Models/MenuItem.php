<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    // Table name if it's not the plural of the model
    protected $table = 'menu_items';
    
    // Primary key if it's not 'id'
    protected $primaryKey = 'MenuItemID';
    
    protected $fillable = [
        // ... other attributes
        'StocksAvailable',
    ];
} 