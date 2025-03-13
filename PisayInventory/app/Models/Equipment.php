<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipment';
    protected $primaryKey = 'equipment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'equipment_id',
        'equipment_name',
        'laboratory_id',
        'description',
        'serial_number',
        'model_number',
        'condition',
        'status',
        'acquisition_date',
        'last_maintenance_date',
        'next_maintenance_date',
        'created_by',
        'updated_by',
        'deleted_by',
        'RestoredById',
        'DateRestored',
        'IsDeleted'
    ];

    protected $dates = [
        'acquisition_date',
        'last_maintenance_date',
        'next_maintenance_date',
        'created_at',
        'updated_at',
        'deleted_at',
        'DateRestored'
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'DateRestored' => 'datetime',
        'IsDeleted' => 'boolean'
    ];

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class, 'laboratory_id', 'laboratory_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(UserAccount::class, 'created_by', 'UserAccountID');
    }

    public function updatedBy()
    {
        return $this->belongsTo(UserAccount::class, 'updated_by', 'UserAccountID');
    }

    public function deletedBy()
    {
        return $this->belongsTo(UserAccount::class, 'deleted_by', 'UserAccountID');
    }

    public function restoredBy()
    {
        return $this->belongsTo(UserAccount::class, 'RestoredById', 'UserAccountID');
    }

    public function isAvailable()
    {
        return $this->status === 'Available' && !$this->IsDeleted;
    }

    public function isInUse()
    {
        return $this->status === 'In Use';
    }

    public function isUnderMaintenance()
    {
        return $this->status === 'Under Maintenance';
    }

    public function needsMaintenance()
    {
        return $this->status === 'Under Maintenance' || 
               ($this->next_maintenance_date && $this->next_maintenance_date->isPast());
    }

    public function isDamaged()
    {
        return $this->status === 'Damaged';
    }

    // Scope for active records
    public function scopeActive($query)
    {
        return $query->where('IsDeleted', false);
    }

    // Scope for deleted records
    public function scopeDeleted($query)
    {
        return $query->where('IsDeleted', true);
    }

    // Relationships
    public function borrowings()
    {
        return $this->hasMany(EquipmentBorrowing::class, 'equipment_id', 'equipment_id');
    }

    public function creator()
    {
        return $this->belongsTo(UserAccount::class, 'created_by', 'UserAccountID');
    }

    public function modifier()
    {
        return $this->belongsTo(UserAccount::class, 'updated_by', 'UserAccountID');
    }

    public function deleter()
    {
        return $this->belongsTo(UserAccount::class, 'deleted_by', 'UserAccountID');
    }
} 