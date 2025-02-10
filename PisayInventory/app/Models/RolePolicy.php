<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePolicy extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'RolePolicyId';
    
    protected $fillable = [
        'RoleId',
        'Module',
        'CanView',
        'CanAdd',
        'CanEdit',
        'CanDelete',
        'DateCreated',
        'CreatedById',
        'DateModified',
        'ModifiedById',
        'DateDeleted',
        'DeletedById',
        'DateRestored',
        'RestoredById',
        'IsDeleted'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'RoleId', 'RoleId')
                    ->withDefault(['RoleName' => 'Unknown Role']);
    }
    public function created_by_user()
    {
        return $this->belongsTo(UserAccount::class, 'CreatedById', 'UserAccountID');
    }

    public function modified_by_user()
    {
        return $this->belongsTo(UserAccount::class, 'ModifiedById', 'UserAccountID');
    }

    public function deleted_by_user()
    {
        return $this->belongsTo(UserAccount::class, 'DeletedById', 'UserAccountID');
    }

    public function restored_by_user()
    {
        return $this->belongsTo(UserAccount::class, 'RestoredById', 'UserAccountID');
    }
} 