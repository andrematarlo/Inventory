<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Item;
use App\Models\Inventory;

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

    // Accessor for Username
    public function getUsernameAttribute()
    {
        return $this->attributes['Username'];
    }

    // Relationships
    public function created_items()
    {
        return $this->hasMany(Item::class, 'CreatedById', 'UserAccountID');
    }

    public function modified_items()
    {
        return $this->hasMany(Item::class, 'ModifiedById', 'UserAccountID');
    }

    public function deleted_items()
    {
        return $this->hasMany(Item::class, 'DeletedById', 'UserAccountID');
    }

    public function created_inventories()
    {
        return $this->hasMany(Inventory::class, 'CreatedById', 'UserAccountID');
    }

    public function modified_inventories()
    {
        return $this->hasMany(Inventory::class, 'ModifiedById', 'UserAccountID');
    }

    public function deleted_inventories()
    {
        return $this->hasMany(Inventory::class, 'DeletedById', 'UserAccountID');
    }

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