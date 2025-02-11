<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $table = 'role_permissions';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'permission_name',
        'can_view',
        'can_add',
        'can_edit',
        'can_delete',
        'DateCreated',
        'CreatedById',
        'DateModified',
        'ModifiedById',
        'DateDeleted',
        'DateRestored',
        'DeletedById',
        'RestoredById',
        'IsDeleted'
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_add' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
        'IsDeleted' => 'boolean'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'RoleId');
    }
}
