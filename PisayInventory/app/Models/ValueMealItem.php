<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValueMealItem extends Model
{
    protected $fillable = [
        'value_meal_id',
        'menu_item_id',
        'quantity'
    ];

    protected $casts = [
        'quantity' => 'integer'
    ];

    public function valueMeal()
    {
        return $this->belongsTo(MenuItem::class, 'value_meal_id', 'MenuItemID');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id', 'MenuItemID');
    }
} 