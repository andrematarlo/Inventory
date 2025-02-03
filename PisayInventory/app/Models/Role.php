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
        'IsDeleted'
    ];

    public function policies()
    {
        return $this->hasMany(RolePolicy::class, 'RoleId', 'RoleId');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'RoleId', 'RoleId');
    }
} 