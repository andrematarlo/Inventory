<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'unitofmeasure';
    protected $primaryKey = 'UnitOfMeasureId';
    public $timestamps = false;
    public $incrementing = false;  // Disable auto-incrementing

    protected $keyType = 'int';  // Ensure primary key is treated as integer

    protected $attributes = [
        'IsDeleted' => 0
    ];

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
        'IsDeleted'
    ];

    // Add the missing relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedById', 'UserAccountID');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedById', 'UserAccountID');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'DeletedById', 'UserAccountID');
    }
    public function restoredBy()
{
    return $this->belongsTo(UserAccount::class, 'RestoredById', 'UserAccountID');
}
} 