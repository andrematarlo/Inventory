<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
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
        'IsDeleted',
        'image_path'
    ];
    
    protected $casts = [
        'Price' => 'decimal:2',
        'IsAvailable' => 'boolean',
        'IsDeleted' => 'boolean',
        'StocksAvailable' => 'integer'
    ];
    
    /**
     * Accessor for name attribute to map to ItemName column
     */
    public function getNameAttribute()
    {
        return $this->ItemName;
    }
    
    /**
     * Mutator for name attribute to map to ItemName column
     */
    public function setNameAttribute($value)
    {
        $this->attributes['ItemName'] = $value;
    }
    
    /**
     * Accessor for available attribute to map to IsAvailable column
     */
    public function getAvailableAttribute()
    {
        return $this->IsAvailable;
    }
    
    /**
     * Mutator for available attribute to map to IsAvailable column
     */
    public function setAvailableAttribute($value)
    {
        $this->attributes['IsAvailable'] = $value;
    }
    
    /**
     * Accessor for image attribute to map to image_path column
     */
    public function getImageAttribute()
    {
        return $this->image_path;
    }
    
    /**
     * Mutator for image attribute to map to image_path column
     */
    public function setImageAttribute($value)
    {
        $this->attributes['image_path'] = $value;
    }
    
    /**
     * Accessor for category attribute to map to ClassificationId column
     */
    public function getCategoryAttribute()
    {
        return $this->ClassificationId;
    }
    
    /**
     * Mutator for category attribute to map to ClassificationId column
     */
    public function setCategoryAttribute($value)
    {
        $this->attributes['ClassificationId'] = $value;
    }
    
    /**
     * Accessor for id attribute to map to MenuItemID
     */
    public function getIdAttribute()
    {
        return $this->MenuItemID;
    }
    
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
        return $this->belongsTo(Classification::class, 'ClassificationID', 'ClassificationId');
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

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'menu_item_id', 'MenuItemID');
    }
} 