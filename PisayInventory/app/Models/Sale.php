<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Sale extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'SaleId';
    public $timestamps = false;

    protected $fillable = [
        'ItemId',
        'Quantity',
        'UnitPrice',
        'TotalPrice',
        'CustomerName',
        'DateSold',
        'CreatedById',
        'IsDeleted'
    ];

    protected $dates = [
        'DateSold'
    ];

    // Relationships
    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemId', 'ItemId');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'CreatedById', 'UserAccountID');
    }

    // Scope for active sales
    public function scopeActive($query)
    {
        return $query->where('IsDeleted', false);
    }

    // Soft delete method
    public function softDelete()
    {
        $this->IsDeleted = true;
        $this->save();
    }

    // Restore method
    public function restore()
    {
        $this->IsDeleted = false;
        $this->save();
    }

    // Mutator for DateSold
    public function setDateSoldAttribute($value)
    {
        $this->attributes['DateSold'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    // Accessor for TotalPrice
    public function getTotalPriceAttribute($value)
    {
        return number_format($value, 2);
    }
}
