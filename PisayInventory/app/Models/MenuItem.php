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
        'ClassificationID',
        'UnitOfMeasureID',
        'IsAvailable',
        'IsDeleted'
    ];
    
    protected $casts = [
        'Price' => 'decimal:2',
        'IsAvailable' => 'boolean',
        'IsDeleted' => 'boolean'
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
        return $this->belongsTo(Classification::class, 'ClassificationID', 'ClassificationID');
    }
    
    public function unitOfMeasure()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UnitOfMeasureID', 'UnitOfMeasureID')
                    ->withDefault(['UnitName' => 'N/A']);
    }
} 