<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Equipment;

class Laboratory extends Model
{
    use HasFactory;

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
    protected $primaryKey = 'LabID';

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
        'LabName',
        'LabNumber',
        'Building',
        'Floor',
        'Capacity',
        'Description',
        'Status',
        'EquipmentCount'
    ];

    /**
     * Get the equipment in the laboratory.
     */
    public function equipment()
    {
        return $this->hasMany(Equipment::class, 'LabID', 'LabID');
    }

    /**
     * Scope a query to only include active laboratories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('Status', 'Active');
    }

    /**
     * Scope a query to only include inactive laboratories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('Status', 'Inactive');
    }

    /**
     * Scope a query to only include laboratories under maintenance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnderMaintenance($query)
    {
        return $query->where('Status', 'Under Maintenance');
    }

    /**
     * Get the full location of the laboratory.
     *
     * @return string
     */
    public function getFullLocationAttribute()
    {
        return "{$this->Building}, Floor {$this->Floor}, Room {$this->LabNumber}";
    }
} 