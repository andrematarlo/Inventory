<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates for each action
        Gate::define('view', function ($user, $module) {
            return $this->checkPermission($user, $module, 'view');
        });

        Gate::define('add', function ($user, $module) {
            return $this->checkPermission($user, $module, 'add');
        });

        Gate::define('edit', function ($user, $module) {
            return $this->checkPermission($user, $module, 'edit');
        });

        Gate::define('delete', function ($user, $module) {
            return $this->checkPermission($user, $module, 'delete');
        });
    }

    /**
     * Check if the user has permission for the given action on the module
     */
    private function checkPermission($user, $module, $action): bool
    {
        $policy = \App\Models\RolePolicy::where('RoleId', $user->RoleId)
            ->where('Module', $module)
            ->where('IsDeleted', 0)
            ->first();

        if (!$policy) {
            return false;
        }

        $permissionColumn = 'Can' . ucfirst($action);
        return (bool) ($policy->$permissionColumn ?? false);
    }
}
