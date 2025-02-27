<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use App\Models\RolePolicy;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getUserPermissions($module)
    {
        try {
            if (!Auth::check()) {
                return (object)[
                    'CanAdd' => false,
                    'CanEdit' => false,
                    'CanDelete' => false,
                    'CanView' => false
                ];
            }

            // Get the employee record for the authenticated user
            $employee = Auth::user()->employee;
            if (!$employee) {
                Log::warning("No employee record found for user: " . Auth::id());
                return (object)[
                    'CanAdd' => false,
                    'CanEdit' => false,
                    'CanDelete' => false,
                    'CanView' => false
                ];
            }
            
            // Get all roles for the employee
            $roles = $employee->roles()
                ->where('roles.IsDeleted', false)
                ->get();

            // Initialize default permissions
            $mergedPermissions = [
                'CanAdd' => false,
                'CanEdit' => false,
                'CanDelete' => false,
                'CanView' => false
            ];

            // Merge permissions from all roles
            foreach ($roles as $role) {
                $policy = RolePolicy::where('RoleId', $role->RoleId)
                    ->where('Module', $module)
                    ->where('IsDeleted', false)
                    ->first();

                if ($policy) {
                    $mergedPermissions['CanAdd'] |= $policy->CanAdd;
                    $mergedPermissions['CanEdit'] |= $policy->CanEdit;
                    $mergedPermissions['CanDelete'] |= $policy->CanDelete;
                    $mergedPermissions['CanView'] |= $policy->CanView;
                }
            }

            return (object)$mergedPermissions;

        } catch (\Exception $e) {
            Log::error("Error getting user permissions: " . $e->getMessage());
            return (object)[
                'CanAdd' => false,
                'CanEdit' => false,
                'CanDelete' => false,
                'CanView' => false
            ];
        }
    }

    protected function respondWithSuccess($message, $data = null)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    protected function respondWithError($message, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $code);
    }
}
