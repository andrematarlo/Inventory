<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UserAccount extends Authenticatable
{
    use Notifiable;

    protected $table = 'UserAccount';
    protected $primaryKey = 'UserAccountID';
    public $timestamps = false;

    protected $fillable = [
        'Username',
        'Password',
        'IsDeleted',
        'DateCreated',
        'CreatedById',
        'DateModified',
        'ModifiedById',
        'DateDeleted',
        'DeletedById'
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
        return $this->{$this->primaryKey};
    }

    public function getAuthPassword()
    {
        return $this->Password;
    }

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

    // Override the default username column for authentication
    public function username()
    {
        return 'Username';
    }
}