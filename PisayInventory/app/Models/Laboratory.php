<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Equipment;

class Laboratory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'laboratories';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'laboratory_id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'laboratory_id',
        'laboratory_name',
        'description',
        'location',
        'capacity',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        'RestoredById',
        'DateRestored',
        'IsDeleted',
        'created_at',
        'updated_at',
        'deleted_at',
        'role',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'capacity' => 'integer',
        'IsDeleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'DateRestored' => 'datetime'
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'Available',
        'IsDeleted' => false
    ];

    /**
     * Get the equipment in the laboratory.
     */
    public function equipment()
    {
        return $this->hasMany(Equipment::class, 'laboratory_id', 'laboratory_id');
    }

    /**
     * Get the reservations for the laboratory.
     */
    public function reservations()
    {
        return $this->hasMany(LaboratoryReservation::class, 'laboratory_id', 'laboratory_id');
    }

    /**
     * Get the active reservations for the laboratory.
     */
    public function activeReservations()
    {
        return $this->reservations()->where('status', 'Active');
    }

    /**
     * Get the user who created the laboratory.
     */
    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the user who modified the laboratory.
     */
    public function updated_by_user()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * Get the user who deleted the laboratory.
     */
    public function deleted_by_user()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id');
    }

    /**
     * Get the user who restored the laboratory.
     */
    public function restored_by_user()
    {
        return $this->belongsTo(User::class, 'RestoredById', 'id');
    }

    /**
     * Scope a query to only include active laboratories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope a query to only include inactive laboratories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'Inactive');
    }

    /**
     * Scope a query to only include laboratories under maintenance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnderMaintenance($query)
    {
        return $query->where('status', 'Under Maintenance');
    }

    /**
     * Get the full location of the laboratory.
     *
     * @return string
     */
    public function getFullLocationAttribute()
    {
        return "{$this->location}";
    }
} 