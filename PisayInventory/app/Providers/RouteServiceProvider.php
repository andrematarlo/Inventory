<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        // Add route parameter patterns
        Route::pattern('id', '[0-9]+');
        Route::pattern('slug', '[a-z0-9-]+');

        // Add route model bindings
        Route::model('supplier', \App\Models\Supplier::class);
        Route::model('item', \App\Models\Item::class);
        Route::model('classification', \App\Models\Classification::class);
        Route::model('unit', \App\Models\Unit::class);
        Route::model('role', \App\Models\Role::class);
        Route::model('employee', \App\Models\Employee::class);
    }
}
