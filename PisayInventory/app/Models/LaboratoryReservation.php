<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryReservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'reservation_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'reservation_id',
        'laboratory_id',
        'reserver_id',
        'reservation_date',
        'start_time',
        'end_time',
        'purpose',
        'num_students',
        'status',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by',
        'RestoredById',
        'DateRestored',
        'IsDeleted'
    ];

    protected $dates = [
        'reservation_date',
        'start_time',
        'end_time',
        'created_at',
        'updated_at',
        'deleted_at',
        'DateRestored'
    ];

    protected $casts = [
        'IsDeleted' => 'boolean'
    ];

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class, 'laboratory_id', 'laboratory_id');
    }

    public function reserver()
    {
        return $this->belongsTo(User::class, 'reserver_id', 'UserAccountID');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id');
    }

    public function restoredBy()
    {
        return $this->belongsTo(User::class, 'RestoredById', 'id');
    }

    public function isActive()
    {
        return $this->status === 'Active';
    }

    public function isPending()
    {
        return $this->status === 'Pending';
    }

    public function isCompleted()
    {
        return $this->status === 'Completed';
    }

    public function isCancelled()
    {
        return $this->status === 'Cancelled';
    }

    public function isUpcoming()
    {
        return $this->reservation_date > now()->toDateString();
    }

    public function canBeCancelled()
    {
        return $this->isActive() && $this->isUpcoming();
    }

    public function getRouteKeyName()
    {
        return 'reservation_id';
    }
} 