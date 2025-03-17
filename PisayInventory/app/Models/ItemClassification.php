<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemClassification extends Model
{
    protected $table = 'itemclassification';
    protected $primaryKey = 'ClassificationID';
    public $timestamps = false;

    protected $fillable = [
        'ClassificationName',
        'IsDeleted'
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'ClassificationID', 'ClassificationID');
    }
} 