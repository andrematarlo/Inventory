<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Purchase extends Model
{
    protected $table = 'purchases';
    protected $primaryKey = 'PurchaseId';
    public $timestamps = false;

    protected $fillable = [
        'ItemId',
        'UnitOfMeasureId',
        'ClassificationId',
        'Quantity',
        'StocksAdded',
        'DateCreated',
        'CreatedById',
        'DateModified',
        'ModifiedById',
        'DateDeleted',
        'DeletedById',
        'IsDeleted'
    ];

    protected $dates = [
        'DateCreated',
        'DateModified',
        'DateDeleted'
    ];

    // Relationships
    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemId', 'ItemId');
    }

    public function unit_of_measure()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UnitOfMeasureId', 'UnitOfMeasureId');
    }

    public function classification()
    {
        return $this->belongsTo(Classification::class, 'ClassificationId', 'ClassificationId');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'CreatedById', 'UserAccountID');
    }

    public function modified_by_user()
    {
        return $this->belongsTo(User::class, 'ModifiedById', 'UserAccountID');
    }

    public function deleted_by_user()
    {
        return $this->belongsTo(User::class, 'DeletedById', 'UserAccountID');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('IsDeleted', 0);
    }

    public function scopeDeleted($query)
    {
        return $query->where('IsDeleted', 1);
    }

    // Soft delete method
    public function softDelete()
    {
        $this->IsDeleted = 1;
        $this->DateDeleted = now();
        $this->DeletedById = auth()->id();
        $this->save();
    }

    // Restore method
    public function restore()
    {
        $this->IsDeleted = 0;
        $this->DateDeleted = null;
        $this->DeletedById = null;
        $this->save();
    }

    // Mutators
    public function setDateCreatedAttribute($value)
    {
        $this->attributes['DateCreated'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function setDateModifiedAttribute($value)
    {
        $this->attributes['DateModified'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
