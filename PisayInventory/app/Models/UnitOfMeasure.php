<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitOfMeasure extends Model
{
    protected $table = 'UnitOfMeasure';
    protected $primaryKey = 'UnitOfMeasureId';
    public $timestamps = false;

    protected $fillable = [
        'UnitName',
        'CreatedById',
        'DateCreated'
    ];

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