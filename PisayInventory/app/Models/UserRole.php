<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'userroles';
    protected $primaryKey = 'UserRoleId';
    public $timestamps = false;

    protected $fillable = [
        'UserAccountId',
        'RoleId',
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
        'DateDeleted' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserAccountId', 'UserAccountId');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'RoleId', 'RoleId');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedById', 'UserAccountId');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'ModifiedById', 'UserAccountId');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'DeletedById', 'UserAccountId');
    }
} 