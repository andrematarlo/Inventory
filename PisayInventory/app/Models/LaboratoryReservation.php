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
        'control_no',
        'laboratory_id',
        'reserver_id',
        'campus',
        'school_year',
        'grade_section',
        'subject',
        'teacher_id',
        'reservation_date',
        'start_time',
        'end_time',
        'num_students',
        'requested_by_type',
        'requested_by',
        'date_requested',
        'group_members',
        'endorsement_status',
        'endorsed_by',
        'endorsed_at',
        'endorser_role',
        'approved_by',
        'approved_at',
        'approver_role',
        'disapproved_by',
        'disapproved_at',
        'disapprover_role',
        'status',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by',
        'RestoredById',
        'DateRestored',
        'IsDeleted',
        'reservation_date_from',
        'reservation_date_to'
    ];

    protected $dates = [
        'reservation_date',
        'date_requested',
        'start_time',
        'end_time',
        'created_at',
        'updated_at',
        'deleted_at',
        'DateRestored'
    ];

    protected $casts = [
        'IsDeleted' => 'boolean',
        'group_members' => 'array'
    ];

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class, 'laboratory_id', 'laboratory_id');
    }

    public function reserver()
    {
        return $this->belongsTo(User::class, 'reserver_id', 'UserAccountID');
    }

    public function teacher()
    {
        return $this->belongsTo(Employee::class, 'teacher_id', 'EmployeeID');
    }

    public function endorsedBy()
    {
        return $this->belongsTo(Employee::class, 'endorsed_by', 'EmployeeID');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by', 'EmployeeID');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'UserAccountID');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'UserAccountID');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'UserAccountID');
    }

    public function restoredBy()
    {
        return $this->belongsTo(User::class, 'RestoredById', 'UserAccountID');
    }

    public function isForApproval()
    {
        return $this->status === 'For Approval';
    }

    public function isApproved()
    {
        return $this->status === 'Approved';
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
        return $this->isApproved() && $this->isUpcoming();
    }

    public function getRouteKeyName()
    {
        return 'reservation_id';
    }

    public function endorser()
    {
        return $this->belongsTo(Employee::class, 'endorsed_by', 'EmployeeID');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by', 'EmployeeID');
    }

    public function disapprover()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'disapproved_by', 'EmployeeID')
            ->withDefault()
            ->select(['EmployeeID', 'FirstName', 'LastName']);
    }
}