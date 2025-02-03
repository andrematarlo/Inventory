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
        'role',
        'DateCreated',
        'CreatedById',
        'DateModified',
        'ModifiedById',
        'DateDeleted',
        'DeletedById',
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
        return $this->belongsTo(UserAccount::class, 'CreatedById', 'UserAccountID');
    }

    // Relationship with modifier
    public function modifier()
    {
        return $this->belongsTo(UserAccount::class, 'ModifiedById', 'UserAccountID');
    }

    // Relationship with deleter
    public function deleter()
    {
        return $this->belongsTo(UserAccount::class, 'DeletedById', 'UserAccountID');
    }

    // Get full name attribute
    public function getFullNameAttribute()
    {
        return "{$this->FirstName} {$this->LastName}";
    }
} 