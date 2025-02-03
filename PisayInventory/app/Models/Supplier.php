<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'SupplierID';
    public $timestamps = false;

    protected $fillable = [
        'SupplierName',
        'ContactNum',
        'Address',
        'CreatedById',
        'DateCreated',
        'ModifiedById',
        'DateModified',
        'DeletedById',
        'DateDeleted',
        'IsDeleted'
    ];

    // Add a boot method to ensure SupplierName is never null
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            if (empty($supplier->SupplierName)) {
                $supplier->SupplierName = 'Unknown Supplier';
            }
        });
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'SupplierID', 'SupplierID')
                    ->where('IsDeleted', 0);
    }
} 