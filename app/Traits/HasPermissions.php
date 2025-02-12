<?php

namespace App\Traits;

use App\Models\RolePolicy;
use Illuminate\Support\Facades\Log;

trait HasPermissions
{
    public function hasPermission(string $module, string $action): bool
    {
        // Debug the values
        Log::info('Checking permission:', [
            'user_role' => $this->role,
            'module' => $module,
            'action' => $action,
            'user' => [
                'id' => $this->UserAccountID,
                'username' => $this->Username,
                'role' => $this->role
            ]
        ]);

        // First check if any policies exist for this role
        $allPolicies = RolePolicy::where('RoleId', $this->role)
            ->where('IsDeleted', 0)
            ->get();
            
        Log::info('All policies for role:', [
            'role' => $this->role,
            'policies' => $allPolicies->toArray()
        ]);

        $permission = RolePolicy::where('RoleId', $this->role)
            ->where('ModuleName', 'LIKE', $module)  // Case-insensitive comparison
            ->where('IsDeleted', 0)
            ->first();

        if (!$permission) {
            Log::info('No permission found for:', [
                'role' => $this->role,
                'module' => $module,
                'action' => $action
            ]);
            return false;
        }

        $hasPermission = match ($action) {
            'view' => (bool) $permission->CanView,
            'add' => (bool) $permission->CanAdd,
            'edit' => (bool) $permission->CanEdit,
            'delete' => (bool) $permission->CanDelete,
            default => false,
        };

        Log::info('Permission check result:', [
            'role' => $this->role,
            'module' => $module,
            'action' => $action,
            'result' => $hasPermission,
            'permission_record' => $permission->toArray()
        ]);

        return $hasPermission;
    }
}
