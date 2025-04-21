<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaboratoryAccountabilityItem extends Model
{
    protected $fillable = [
        'laboratory_accountability_id',
        'quantity',
        'item',
        'description',
        'issued_condition',
        'returned_condition'
    ];

    public function accountability()
    {
        return $this->belongsTo(LaboratoryAccountability::class, 'laboratory_accountability_id');
    }
} 