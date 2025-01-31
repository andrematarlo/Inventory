<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'Suppliers';
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

    public function items()
    {
        return $this->hasMany(Item::class, 'SupplierID');
    }
} 