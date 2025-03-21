<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activities';

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'details'
    ];

    protected $casts = [
        'details' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'UserAccountID');
    }
} 