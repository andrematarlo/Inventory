<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'unitofmeasure';
    protected $primaryKey = 'UnitOfMeasureId';
    public $timestamps = false;

    protected $fillable = [
        'UnitOfMeasureId',
        'UnitName',
        'CreatedById',
        'DateCreated',
        'ModifiedById',
        'DateModified',
        'DeletedById',
        'DateDeleted',
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
} 