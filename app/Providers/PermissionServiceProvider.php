<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Models\RolePolicy;
use Illuminate\Support\Facades\Auth;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add @can directive for checking permissions
        Blade::if('can', function ($action, $module) {
            if (!Auth::check()) {
                return false;
            }

            $user = Auth::user();
            $roleId = $user->RoleId;

            $policy = RolePolicy::where('RoleId', $roleId)
                ->where('Module', $module)
                ->where('IsDeleted', 0)
                ->first();

            if (!$policy) {
                return false;
            }

            $permissionColumn = 'Can' . ucfirst($action);
            return $policy->$permissionColumn ?? false;
        });
    }
}
