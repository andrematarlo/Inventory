<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnitOfMeasure extends Model
{
    protected $table = 'unitofmeasure';
    protected $primaryKey = 'UnitOfMeasureId';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'UnitOfMeasureId',
        'UnitName',
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

    protected $dates = [
        'DateCreated',
        'DateModified',
        'DateDeleted'
    ];

    // Override the default table name resolution
    public function getTable()
    {
        return 'unitofmeasure';
    }

    // Scope for active units
    public function scopeActive($query)
    {
        return $query->where('IsDeleted', 0);
    }

    // Relationships
    public function created_by()
    {
        return $this->belongsTo(User::class, 'CreatedById', 'UserAccountID');
    }

    public function modified_by()
    {
        return $this->belongsTo(User::class, 'ModifiedById', 'UserAccountID');
    }

    public function deleted_by()
    {
        return $this->belongsTo(User::class, 'DeletedById', 'UserAccountID');
    }
    public function restored_By()
    {
        return $this->belongsTo(UserAccount::class, 'RestoredById', 'UserAccountID');
    }

    // Relationship for items that use this unit of measure
    public function items()
    {
        return $this->hasMany(Item::class, 'UnitOfMeasureId', 'UnitOfMeasureId');
    }

    // Safe fetching method
    public static function safeGetUnits()
    {
        try {
            // Try model query first
            return self::active()->orderBy('UnitName')->get();
        } catch (\Exception $e) {
            try {
                // Fallback to direct database query
                return DB::table('unitofmeasure')
                    ->where('IsDeleted', 0)
                    ->orderBy('UnitName')
                    ->get();
            } catch (\Exception $dbError) {
                // Log error and return empty collection
                Log::error('Unit of Measure fetch failed: ' . $dbError->getMessage());
                return collect();
            }
        }
    }
} 