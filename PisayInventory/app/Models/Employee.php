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
        'DateCreated',
        'CreatedByID',
        'ModifiedByID',
        'DateModified',
        'DeletedByID',
        'RestoredById',
        'DateDeleted',
        'DateRestored',
        'IsDeleted'
    ];

    protected $casts = [
        'DateCreated' => 'datetime',
        'DateModified' => 'datetime',
        'DateDeleted' => 'datetime',
        'DateRestored' => 'datetime',
        'IsDeleted' => 'boolean'
    ];

    // Define relationships
    public function userAccount()
    {
        return $this->belongsTo(User::class, 'UserAccountID', 'UserAccountID');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'employee_roles', 'EmployeeId', 'RoleId')
            ->where('employee_roles.IsDeleted', false)
            ->withPivot([
                'id',
                'IsDeleted',
                'DateCreated',
                'CreatedById',
                'DateModified',
                'ModifiedById',
                'DateDeleted',
                'DeletedById',
                'DateRestored',
                'RestoredById'
            ]);
    }

    public function createdBy()
    {
        \Log::info('CreatedBy Relationship Debug:', [
            'employee_id' => $this->EmployeeID,
            'created_by_id' => $this->CreatedByID,
            'all_attributes' => $this->attributes
        ]);

        return $this->belongsTo(Employee::class, 'CreatedByID', 'EmployeeID')
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
            'modified_by_id' => $this->ModifiedByID,
            'all_attributes' => $this->attributes
        ]);

        return $this->belongsTo(Employee::class, 'ModifiedByID', 'EmployeeID')
            ->select(['EmployeeID', 'FirstName', 'LastName'])
            ->withDefault([
                'EmployeeID' => null,
                'FirstName' => 'System',
                'LastName' => 'User'
            ]);
    }

    public function deletedBy()
    {
        return $this->belongsTo(Employee::class, 'DeletedByID', 'EmployeeID')
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
            'created_by_id' => $this->attributes['CreatedByID'] ?? null,
            'raw_attributes' => array_intersect_key($this->attributes, array_flip(['CreatedByID', 'ModifiedByID']))
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
            'modified_by_id' => $this->attributes['ModifiedByID'] ?? null,
            'raw_attributes' => array_intersect_key($this->attributes, array_flip(['CreatedByID', 'ModifiedByID']))
        ]);
        
        $modifier = $this->modifiedBy;
        return $modifier ? "{$modifier->FirstName} {$modifier->LastName}" : 'Unknown User';
    }

    // Helper method to get role names as string
    public function getRoleNamesAttribute()
    {
        return $this->roles->pluck('RoleName')->implode(', ');
    }

    // Add this relationship if it doesn't exist
    public function user()
    {
        return $this->belongsTo(User::class, 'UserAccountID', 'UserAccountID');
    }

    // Add this scope to help with debugging
    public function scopeWithUserAccount($query)
    {
        return $query->leftJoin('user_account', 'employee.UserAccountID', '=', 'user_account.UserAccountID')
                     ->select('employee.*', 'user_account.Username');
    }
} 