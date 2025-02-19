<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Enums\PurchaseStatus;

class Purchase extends Model
{
    protected $table = 'purchases';
    protected $primaryKey = 'PurchaseId';
    public $timestamps = false;

    protected $fillable = [
        'ItemId',
        'SupplierId',
        'Quantity',
        'UnitPrice',
        'TotalAmount',
        'PurchaseOrderNumber',
        'PurchaseDate',
        'DeliveryDate',
        'Status',
        'Notes',
        'CreatedById',
        'DateCreated',
        'ModifiedById',
        'DateModified',
        'DeletedById',
        'DateDeleted',
        'IsDeleted'
    ];

    protected $dates = [
        'PurchaseDate',
        'DeliveryDate',
        'DateCreated',
        'DateModified',
        'DateDeleted'
    ];

    protected $casts = [
        'Quantity' => 'integer',
        'UnitPrice' => 'decimal:2',
        'TotalAmount' => 'decimal:2',
        'IsDeleted' => 'boolean',
        'PurchaseDate' => 'date',
        'DeliveryDate' => 'date',
        'Status' => PurchaseStatus::class,
        'OrderDate' => 'datetime',
        'DateCreated' => 'datetime',
        'DateModified' => 'datetime',
        'DateDeleted' => 'datetime',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('IsDeleted', false);
    }

    public function scopeWithCustomTrashed($query)
    {
        return $query;
    }

    public function scopeOnlyTrashed($query)
    {
        return $query->where('IsDeleted', true);
    }

    // Custom soft delete methods
    public function softDelete()
    {
        $this->IsDeleted = true;
        $this->DateDeleted = now();
        $this->DeletedById = Auth::id();
        return $this->save();
    }

    public function restore()
    {
        $this->IsDeleted = false;
        $this->DateDeleted = null;
        $this->DeletedById = null;
        return $this->save();
    }

    // Relationships
    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemId', 'ItemId');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierId', 'SupplierID');
    }

    public function created_by()
    {
        return $this->belongsTo(UserAccount::class, 'CreatedById', 'UserAccountID');
    }

    public function modified_by()
    {
        return $this->belongsTo(UserAccount::class, 'ModifiedById', 'UserAccountID');
    }

    public function deleted_by()
    {
        return $this->belongsTo(UserAccount::class, 'DeletedById', 'UserAccountID');
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
