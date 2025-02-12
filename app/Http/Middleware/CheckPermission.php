<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission = null)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $userRole = Auth::user()->role;
        
        // If user is Admin, allow all actions
        if ($userRole === 'Admin') {
            return $next($request);
        }

        // If user is Inventory Manager, allow all actions except user management
        if ($userRole === 'Inventory Manager') {
            return $next($request);
        }

        // For Inventory Staff, only allow view actions
        if ($userRole === 'Inventory Staff') {
            if ($permission && $permission !== 'view') {
                return redirect()->back()
                    ->with('error', 'You do not have permission to perform this action.');
            }
        }

        return $next($request);
    }
} 