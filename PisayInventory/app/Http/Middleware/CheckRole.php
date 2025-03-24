<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            return redirect('/login');
        }

        // Split comma-separated roles if they exist
        $allowedRoles = [];
        foreach ($roles as $role) {
            $allowedRoles = array_merge($allowedRoles, explode(',', $role));
        }

        // Check if user has any of the allowed roles
        if (in_array($request->user()->role, $allowedRoles)) {
            return $next($request);
        }

        return redirect()->back()->with('error', 'Unauthorized access.');
    }
} 