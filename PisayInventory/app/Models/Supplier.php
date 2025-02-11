<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'SupplierID';
    public $timestamps = false;

    protected $fillable = [
        'SupplierID',
        'CompanyName',
        'ContactPerson',
        'TelephoneNumber',
        'ContactNum',
        'Address',
        'CreatedById',
        'DateCreated',
        'ModifiedById',
        'DateModified',
        'DeletedById',
        'DateDeleted',
        'RestoredById',
        'DateRestored',
        'IsDeleted'
    ];

    protected $casts = [
        'DateCreated' => 'datetime',
        'DateModified' => 'datetime',
        'DateDeleted' => 'datetime',
        'DateRestored' => 'datetime',
        'IsDeleted' => 'boolean'
    ];

    // Relationships
    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'CreatedById', 'UserAccountID')
                    ->from('UserAccount')
                    ->withDefault(['Username' => 'N/A']);
    }

    public function modified_by_user()
    {
        return $this->belongsTo(User::class, 'ModifiedById', 'UserAccountID')
                    ->from('UserAccount')
                    ->withDefault(['Username' => 'N/A']);
    }

    public function deleted_by_user()
    {
        return $this->belongsTo(User::class, 'DeletedById', 'UserAccountID')
                    ->from('UserAccount')
                    ->withDefault(['Username' => 'N/A']);
    }
    public function restored_by_user()
    {
        return $this->belongsTo(User::class, 'RestoredById', 'UserAccountID')
                    ->from('UserAccount')
                    ->withDefault(['Username' => 'N/A']);
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            if (empty($supplier->CompanyName)) {
                $supplier->CompanyName = 'Unknown Supplier';
            }
        });
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'SupplierID', 'SupplierID')
                    ->where('IsDeleted', 0);
    }

    // Query scopes
    public function scopeActive($query)
    {
        return $query->where('IsDeleted', false);
    }

    public function scopeTrashed($query)
    {
        return $query->where('IsDeleted', true);
    }
} 