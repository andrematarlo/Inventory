<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryManagerAccess
{
    protected $allowedRoutes = [
        'items',
        'inventory',
        'suppliers',
        'classifications',
        'units',
        'reports'
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $userRole = Auth::user()->role;

        // Allow admin full access
        if ($userRole === 'Admin') {
            return $next($request);
        }

        // For Inventory Manager
        if ($userRole === 'Inventory Manager') {
            $currentRoute = $request->segment(1);

            // Check if route is allowed
            if (!in_array($currentRoute, $this->allowedRoutes)) {
                return redirect()->back()->with('error', 'Access denied.');
            }

            // Block delete actions
            if ($request->isMethod('delete')) {
                return redirect()->back()->with('error', 'You do not have permission to delete records.');
            }

            return $next($request);
        }

        return redirect()->back()->with('error', 'Access denied.');
    }
} 