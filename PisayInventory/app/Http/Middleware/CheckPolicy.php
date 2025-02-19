<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPolicy
{
    public function handle(Request $request, Closure $next, ...$policies)
    {
        if (!$request->user() || !$request->user()->hasAnyPolicy($policies)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
} 