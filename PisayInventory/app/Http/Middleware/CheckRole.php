<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // If the user doesn't have auth, redirect to login
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Get the user's role (modify this according to how your roles are stored)
        $userRole = $request->user()->role;
        
        // Check if the user has one of the required roles
        foreach ($roles as $role) {
            if ($userRole === $role) {
                return $next($request);
            }
        }

        // Redirect or abort if user doesn't have the required role
        return redirect()->route('dashboard')->with('error', 'You do not have permission to access this area.');
    }
} 