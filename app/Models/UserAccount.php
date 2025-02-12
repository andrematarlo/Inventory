<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Item;
use App\Models\Inventory;
use App\Models\Role;

class UserAccount extends Authenticatable
{
    use Notifiable;

    protected $table = 'useraccount';
    protected $primaryKey = 'UserAccountID';
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = [
        'UserAccountID',
        'Username',
        'Password',
        'role',
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
    public function restored_items()
    {
    return $this->hasMany(Item::class, 'RestoredById', 'UserAccountID');
    }

    public function restored_inventories()
    {
    return $this->hasMany(Inventory::class, 'RestoredById', 'UserAccountID');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'RoleId', 'RoleId');
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