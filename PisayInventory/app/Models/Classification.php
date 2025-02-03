<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classification extends Model
{
    protected $table = 'classification';
    protected $primaryKey = 'ClassificationId';
    public $timestamps = false;

    protected $fillable = [
        'ClassificationName',
        'ParentClassificationId',
        'DateCreated',
        'CreatedById',
        'DateModified',
        'ModifiedById',
        'DateDeleted',
        'DeletedById',
        'IsDeleted'
    ];

    public function parent()
    {
        return $this->belongsTo(Classification::class, 'ParentClassificationId', 'ClassificationId');
    }

    public function children()
    {
        return $this->hasMany(Classification::class, 'ParentClassificationId', 'ClassificationId');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'ClassificationId', 'ClassificationId');
    }
} 