<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaboratoryReagentItem extends Model
{
    protected $fillable = [
        'laboratory_reagent_request_id',
        'quantity',
        'reagent',
        'sds_checked',
        'issued_amount',
        'remarks'
    ];

    protected $casts = [
        'sds_checked' => 'boolean',
    ];

    public function request()
    {
        return $this->belongsTo(LaboratoryReagentRequest::class, 'laboratory_reagent_request_id');
    }
} 