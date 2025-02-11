<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employee';
    protected $primaryKey = 'EmployeeID';
    public $timestamps = false;
    public $incrementing = true;

    // Remove automatic eager loading as it conflicts with our optimized queries
    // protected $with = ['userAccount', 'createdBy', 'modifiedBy'];

    protected $fillable = [
        'UserAccountID',
        'FirstName',
        'LastName',
        'Address',
        'Email',
        'Gender',
        'Role',
        'CreatedById',
        'DateCreated',
        'ModifiedById',
        'DateModified',
        'DeletedById',
        'DateDeleted',
        'RestoredById',
        'DateRestored',
        'IsDeleted'
    ];

    protected $casts = [
        'IsDeleted' => 'boolean',
        'DateCreated' => 'datetime',
        'DateModified' => 'datetime',
        'DateDeleted' => 'datetime',
    ];

    // Define relationships
    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'UserAccountID', 'UserAccountID');
    }

    public function createdBy()
    {
        \Log::info('CreatedBy Relationship Debug:', [
            'employee_id' => $this->EmployeeID,
            'created_by_id' => $this->CreatedById,
            'all_attributes' => $this->attributes
        ]);

        return $this->belongsTo(Employee::class, 'CreatedById', 'EmployeeID')
            ->select(['EmployeeID', 'FirstName', 'LastName'])
            ->withDefault([
                'EmployeeID' => null,
                'FirstName' => 'System',
                'LastName' => 'User'
            ]);
    }

    public function modifiedBy()
    {
        \Log::info('ModifiedBy Relationship Debug:', [
            'employee_id' => $this->EmployeeID,
            'modified_by_id' => $this->ModifiedById,
            'all_attributes' => $this->attributes
        ]);

        return $this->belongsTo(Employee::class, 'ModifiedById', 'EmployeeID')
            ->select(['EmployeeID', 'FirstName', 'LastName'])
            ->withDefault([
                'EmployeeID' => null,
                'FirstName' => 'System',
                'LastName' => 'User'
            ]);
    }

    public function deletedBy()
    {
        return $this->belongsTo(Employee::class, 'DeletedById', 'EmployeeID')
            ->withDefault([
                'FirstName' => 'System',
                'LastName' => ''
            ]);
    }

    public function restoredBy()
    {
        return $this->belongsTo(Employee::class, 'RestoredById', 'EmployeeID')
            ->withDefault([
                'FirstName' => 'System',
                'LastName' => ''
            ]);
    }

    // Get full name attribute
    public function getFullNameAttribute()
    {
        return "{$this->FirstName} {$this->LastName}";
    }

    // Helper method to get full name of creator
    public function getCreatedByNameAttribute()
    {
        // Get raw values from database
        \Log::info('Raw CreatedBy Values:', [
            'employee_id' => $this->EmployeeID,
            'created_by_id' => $this->attributes['CreatedById'] ?? null,
            'raw_attributes' => array_intersect_key($this->attributes, array_flip(['CreatedById', 'ModifiedById']))
        ]);
        
        $creator = $this->createdBy;
        return $creator ? "{$creator->FirstName} {$creator->LastName}" : 'Unknown User';
    }

    // Helper method to get full name of modifier
    public function getModifiedByNameAttribute()
    {
        // Get raw values from database
        \Log::info('Raw ModifiedBy Values:', [
            'employee_id' => $this->EmployeeID,
            'modified_by_id' => $this->attributes['ModifiedById'] ?? null,
            'raw_attributes' => array_intersect_key($this->attributes, array_flip(['CreatedById', 'ModifiedById']))
        ]);
        
        $modifier = $this->modifiedBy;
        return $modifier ? "{$modifier->FirstName} {$modifier->LastName}" : 'Unknown User';
    }
} 