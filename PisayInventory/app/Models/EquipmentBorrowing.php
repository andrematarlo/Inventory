<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class EquipmentBorrowing extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipment_borrowings';
    protected $primaryKey = 'borrowing_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'borrowing_id',
        'equipment_id',
        'borrower_id',
        'borrow_date',
        'expected_return_date',
        'actual_return_date',
        'purpose',
        'status',
        'condition_on_borrow',
        'condition_on_return',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by',
        'RestoredById',
        'IsDeleted'
    ];

    protected $dates = [
        'borrow_date',
        'expected_return_date',
        'actual_return_date',
        'created_at',
        'updated_at',
        'deleted_at',
        'DateRestored'
    ];

    protected $casts = [
        'IsDeleted' => 'boolean'
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'equipment_id');
    }

    public function borrower()
    {
        return $this->belongsTo(Employee::class, 'borrower_id', 'EmployeeID');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'EmployeeID')
            ->withDefault([
                'FirstName' => 'System',
                'LastName' => 'User'
            ]);
    }

    public function updatedBy()
    {
        return $this->belongsTo(Employee::class, 'updated_by', 'EmployeeID')
            ->withDefault([
                'FirstName' => 'System',
                'LastName' => 'User'
            ]);
    }

    public function deletedBy()
    {
        return $this->belongsTo(Employee::class, 'deleted_by', 'EmployeeID')
            ->withDefault([
                'FirstName' => 'System',
                'LastName' => 'User'
            ]);
    }

    public function restoredBy()
    {
        return $this->belongsTo(Employee::class, 'RestoredById', 'EmployeeID')
            ->withDefault([
                'FirstName' => 'System',
                'LastName' => 'User'
            ]);
    }

    public function getCreatedByNameAttribute()
    {
        return $this->createdBy ? $this->createdBy->FullName : 'System User';
    }

    public function getUpdatedByNameAttribute()
    {
        return $this->updatedBy ? $this->updatedBy->FullName : 'System User';
    }

    public function getDeletedByNameAttribute()
    {
        return $this->deletedBy ? $this->deletedBy->FullName : 'System User';
    }

    public function getRestoredByNameAttribute()
    {
        return $this->restoredBy ? $this->restoredBy->FullName : 'System User';
    }

    public function isOverdue()
    {
        return !$this->actual_return_date && $this->expected_return_date < now();
    }

    public function isReturned()
    {
        return $this->actual_return_date !== null;
    }

    public function isActive()
    {
        return !$this->actual_return_date && !$this->deleted_at;
    }

    public function canBeReturned()
    {
        return !$this->actual_return_date && !$this->deleted_at;
    }

    public function getRouteKeyName()
    {
        return 'borrowing_id';
    }
} 