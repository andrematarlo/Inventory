<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $userRole = Auth::user()->role;

        // Check if user has any of the allowed roles
        if (in_array($userRole, $roles)) {
            // For Inventory Manager, block delete actions
            if ($userRole === 'Inventory Manager' && $request->isMethod('delete')) {
                return redirect()->back()->with('error', 'You do not have permission to delete records.');
            }
            return $next($request);
        }

        return redirect()->back()->with('error', 'You do not have permission to access this page.');
    }
} 