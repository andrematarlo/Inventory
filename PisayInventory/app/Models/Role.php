<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'RoleId';
    
    protected $fillable = [
        'RoleName',
        'Description',
        'DateCreated',
        'CreatedById',
        'DateModified',
        'ModifiedById',
        'DateDeleted',
        'DeletedById',
        'RestoredById',
        'IsDeleted'
    ];

    public function policies()
    {
        return $this->hasMany(RolePolicy::class, 'RoleId', 'RoleId');
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