<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employee';
    protected $primaryKey = 'EmployeeID';
    public $timestamps = false;

    protected $fillable = [
        'UserAccountID',
        'FirstName',
        'LastName',
        'Email',
        'Gender',
        'Address',
        'Role',
        'DateCreated',
        'CreatedByID',
        'ModifiedByID',
        'DateModified',
        'DeletedByID',
        'DateDeleted',
        'IsDeleted'
    ];

    protected $casts = [
        'IsDeleted' => 'boolean',
        'DateCreated' => 'datetime',
        'DateModified' => 'datetime',
        'DateDeleted' => 'datetime',
    ];

    // Relationship with UserAccount
    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'UserAccountID', 'UserAccountID');
    }

    // Relationship with creator
    public function creator()
    {
        return $this->belongsTo(UserAccount::class, 'CreatedByID', 'UserAccountID');
    }

    // Relationship with modifier
    public function modifier()
    {
        return $this->belongsTo(UserAccount::class, 'ModifiedByID', 'UserAccountID');
    }

    // Relationship with deleter
    public function deleter()
    {
        return $this->belongsTo(UserAccount::class, 'DeletedByID', 'UserAccountID');
    }

    // Get full name attribute
    public function getFullNameAttribute()
    {
        return "{$this->FirstName} {$this->LastName}";
    }
} 