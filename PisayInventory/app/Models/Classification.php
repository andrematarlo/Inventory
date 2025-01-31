<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classification extends Model
{
    protected $table = 'Classification';
    protected $primaryKey = 'ClassificationId';
    public $timestamps = false;

    protected $fillable = [
        'ClassificationName',
        'ParentClassificationId'
    ];

    public function parent()
    {
        return $this->belongsTo(Classification::class, 'ParentClassificationId');
    }

    public function children()
    {
        return $this->hasMany(Classification::class, 'ParentClassificationId');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'ClassificationId');
    }
} 