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
        'IsDeleted'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'RoleId', 'RoleId');
    }
} 