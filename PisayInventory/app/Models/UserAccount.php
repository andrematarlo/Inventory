<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use App\Models\Item;
use App\Models\Inventory;
use App\Models\Role;
use Illuminate\Support\Facades\Log;
class UserAccount extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'UserAccountID';
    protected $table = 'useraccount';
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = [
        'Username',
        'Password',
        'Email',
        'EmployeeID',
        'RoleID',
        'role',
        'IsActive',
        'LastLogin',
        'CreatedByID',
        'ModifiedByID',
        'DeletedByID',
        'RestoredById',
        'DateCreated',
        'DateModified',
        'DateDeleted',
        'DateRestored',
        'IsDeleted'
    ];

    protected $hidden = [
        'Password',
        'remember_token',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'IsDeleted' => 'boolean',
        'LastLogin' => 'datetime',
        'DateCreated' => 'datetime',
        'DateModified' => 'datetime',
        'DateDeleted' => 'datetime',
        'DateRestored' => 'datetime'
    ];

    // Add the boot method to handle student roles
protected static function boot()
{
    parent::boot();

    static::creating(function ($userAccount) {
        // If this is being created from a student route
        if (request()->route() && str_contains(request()->route()->getName(), 'students')) {
            // Get the Students role from the database
            $studentRole = Role::where('RoleName', 'Students')
                ->where('IsDeleted', false)
                ->first();

            if ($studentRole) {
                $userAccount->role = $studentRole->RoleName;
                
            } else {
                Log::error('Students role not found in roles table');
                throw new \Exception('Students role not found in the system');
            }
        }
    });
}

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

    public function restored_items()
    {
        return $this->hasMany(Item::class, 'RestoredById', 'UserAccountID');
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

    public function restored_inventories()
    {
        return $this->hasMany(Inventory::class, 'RestoredById', 'UserAccountID');
    }

    /**
     * Get the employee associated with the user account.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID', 'EmployeeID');
    }

    /**
     * Get the role associated with the user account.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'RoleID', 'RoleID');
    }

    /**
     * Get the user who created this account.
     */
    public function createdBy()
    {
        return $this->belongsTo(UserAccount::class, 'CreatedByID', 'UserAccountID');
    }

    /**
     * Get the user who last modified this account.
     */
    public function modifiedBy()
    {
        return $this->belongsTo(UserAccount::class, 'ModifiedByID', 'UserAccountID');
    }

    /**
     * Get the user who deleted this account.
     */
    public function deletedBy()
    {
        return $this->belongsTo(UserAccount::class, 'DeletedByID', 'UserAccountID');
    }

    /**
     * Get the user who restored this account.
     */
    public function restoredBy()
    {
        return $this->belongsTo(UserAccount::class, 'RestoredById', 'UserAccountID');
    }

    // Scope for active records
    public function scopeActive($query)
    {
        return $query->where('IsDeleted', false);
    }

    // Scope for deleted records
    public function scopeDeleted($query)
    {
        return $query->where('IsDeleted', true);
    }

    // Authentication methods
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