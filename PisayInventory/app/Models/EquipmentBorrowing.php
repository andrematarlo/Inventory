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
        'DateRestored'
    ];

    protected $dates = [
        'borrow_date',
        'expected_return_date',
        'actual_return_date',
        'DateRestored',
        'created_at',
        'updated_at',
        'deleted_at'
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
        return $this->belongsTo(UserAccount::class, 'borrower_id', 'UserAccountID');
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

    public function restorer()
    {
        return $this->belongsTo(UserAccount::class, 'RestoredById', 'UserAccountID');
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