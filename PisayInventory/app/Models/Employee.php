<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employee';
    protected $primaryKey = 'EmployeeID';
    public $timestamps = false;

    // Remove automatic eager loading as it conflicts with our optimized queries
    // protected $with = ['userAccount', 'createdBy', 'modifiedBy'];

    protected $fillable = [
        'EmployeeID',
        'UserAccountID',
        'FirstName',
        'LastName',
        'Address',
        'Email',
        'Gender',
        'Role',
        'CreatedByID',
        'DateCreated',
        'ModifiedByID',
        'DateModified',
        'DeletedByID',
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
        $relation = $this->belongsTo(Employee::class, 'CreatedByID', 'EmployeeID')
            ->withDefault([
                'FirstName' => 'Unknown',
                'LastName' => 'User'
            ]);
            
        // Debug log the SQL query
        \Log::info('CreatedBy Query:', [
            'employee_id' => $this->EmployeeID,
            'created_by_id' => $this->CreatedByID,
            'sql' => $relation->toSql(),
            'bindings' => $relation->getBindings()
        ]);
            
        return $relation;
    }

    public function modifiedBy()
    {
        $relation = $this->belongsTo(Employee::class, 'ModifiedByID', 'EmployeeID')
            ->withDefault([
                'FirstName' => 'Unknown',
                'LastName' => 'User'
            ]);
            
        // Debug log the SQL query
        \Log::info('ModifiedBy Query:', [
            'employee_id' => $this->EmployeeID,
            'modified_by_id' => $this->ModifiedByID,
            'sql' => $relation->toSql(),
            'bindings' => $relation->getBindings()
        ]);
            
        return $relation;
    }

    public function deletedBy()
    {
        return $this->belongsTo(Employee::class, 'DeletedByID', 'EmployeeID')
            ->withDefault([
                'FirstName' => 'Unknown',
                'LastName' => 'User'
            ]);
    }
    public function restoredBy()
{
    return $this->belongsTo(UserAccount::class, 'RestoredById', 'UserAccountID');
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
} 