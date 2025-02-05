<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('IsDeleted', 0);
    }

    public function scopeWithCustomTrashed($query)
    {
        return $query;
    }

    public function scopeOnlyTrashed($query)
    {
        return $query->where('IsDeleted', 1);
    }

    // Custom soft delete methods
    public function softDelete()
    {
        $this->IsDeleted = 1;
        $this->DateDeleted = now();
        $this->DeletedById = Auth::id();
        return $this->save();
    }

    public function restore()
    {
        $this->IsDeleted = 0;
        $this->DateDeleted = null;
        $this->DeletedById = null;
        return $this->save();
    }

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

    // Mutators
    public function setDateCreatedAttribute($value)
    {
        $this->attributes['DateCreated'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function setDateModifiedAttribute($value)
    {
        $this->attributes['DateModified'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public static function boot()
    {
        parent::boot();

        // Add a macro to handle withTrashed method
        static::macro('withTrashed', function () {
            return $this;
        });

        // Add a macro to the query builder
        Builder::macro('withTrashed', function () {
            return $this;
        });
    }
}
