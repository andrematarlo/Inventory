<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class UserAccount extends Authenticatable
{
    use Notifiable;

    protected $table = 'useraccount';
    protected $primaryKey = 'UserAccountID';
    public $timestamps = false;

    protected $fillable = [
        'Username',
        'Password',
        'role',
        'DateCreated',
        'CreatedById',
        'DateModified',
        'ModifiedById',
        'DateDeleted',
        'DeletedById',
        'IsDeleted'
    ];

    protected $hidden = [
        'Password',
    ];

    protected $casts = [
        'IsDeleted' => 'boolean',
        'DateCreated' => 'datetime',
        'DateModified' => 'datetime',
        'DateDeleted' => 'datetime',
    ];

    // Authentication methods
    public function getAuthIdentifierName()
    {
        return $this->primaryKey;
    }

    public function getAuthIdentifier()
    {
        Log::info('Auth identifier check', [
            'primary_key' => $this->primaryKey,
            'value' => $this->{$this->primaryKey},
            'raw_attributes' => $this->attributes
        ]);
        
        return $this->{$this->primaryKey};
    }

    public function getAuthPassword()
    {
        return $this->Password;
    }

    // No remember token needed
    public function getRememberTokenName()
    {
        return '';
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
    }

    // Relationships
    public function employee()
    {
        return $this->hasOne(Employee::class, 'UserAccountID', 'UserAccountID');
    }

    // Accessors
    public function getUsernameAttribute($value)
    {
        return $value;
    }

    // Override the default username column for authentication
    public function username()
    {
        return 'Username';
    }

    public function creator()
    {
        return $this->belongsTo(UserAccount::class, 'CreatedByID', 'UserAccountID');
    }

    public function modifier()
    {
        return $this->belongsTo(UserAccount::class, 'ModifiedByID', 'UserAccountID');
    }

    public function deleter()
    {
        return $this->belongsTo(UserAccount::class, 'DeletedByID', 'UserAccountID');
    }
} 