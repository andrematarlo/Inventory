<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use SoftDeletes;

    protected $table = 'menu_items';
    protected $primaryKey = 'MenuItemID';
    
    protected $fillable = [
        'ItemName',
        'Description',
        'Price',
        'ClassificationId',
        'UnitOfMeasureID',
        'StocksAvailable',
        'IsAvailable',
        'IsDeleted'
    ];
    
    protected $casts = [
        'Price' => 'decimal:2',
        'IsAvailable' => 'boolean',
        'IsDeleted' => 'boolean',
        'StocksAvailable' => 'integer'
    ];
    
    /**
     * Scope a query to only include active menu items.
     */
    public function scopeActive($query)
    {
        return $query->where('IsDeleted', false)
                    ->where('IsAvailable', true);
    }
    
    public function classification()
    {
        return $this->belongsTo(Classification::class, 'ClassificationId', 'ClassificationId');
    }
    
    public function unitOfMeasure()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UnitOfMeasureID', 'UnitOfMeasureID')
                    ->withDefault(['UnitName' => 'N/A']);
    }
    
    /**
     * Check if the item has sufficient stock.
     *
     * @param int $quantity
     * @return bool
     */
    public function hasSufficientStock($quantity = 1)
    {
        return $this->StocksAvailable >= $quantity;
    }
    
    /**
     * Decrement the stock by the given quantity.
     *
     * @param int $quantity
     * @return bool
     */
    public function decrementStock($quantity = 1)
    {
        if ($this->hasSufficientStock($quantity)) {
            $this->decrement('StocksAvailable', $quantity);
            return true;
        }
        return false;
    }
    
    /**
     * Increment the stock by the given quantity.
     *
     * @param int $quantity
     * @return void
     */
    public function incrementStock($quantity = 1)
    {
        $this->increment('StocksAvailable', $quantity);
    }
} 