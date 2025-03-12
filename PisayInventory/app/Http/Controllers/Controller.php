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
use App\Models\Role;

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

        $user = Auth::user();

        // Get the employee record for the authenticated user
        $employee = $user->employee;
        
        // If user is an employee, use existing employee role logic
        if ($employee) {
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
        }
        // If user is a student, check student role permissions
        else if ($user->role === 'Students') {
            $studentRole = Role::where('RoleName', 'Students')
                ->where('IsDeleted', false)
                ->first();

            if ($studentRole) {
                $policy = RolePolicy::where('RoleId', $studentRole->RoleId)
                    ->where('Module', $module)
                    ->where('IsDeleted', false)
                    ->first();

                if ($policy) {
                    return (object)[
                        'CanAdd' => $policy->CanAdd,
                        'CanEdit' => $policy->CanEdit,
                        'CanDelete' => $policy->CanDelete,
                        'CanView' => $policy->CanView
                    ];
                }
            }
        }

        // Default return for non-employees and non-students
        Log::warning("No valid role found for user: " . Auth::id());
        return (object)[
            'CanAdd' => false,
            'CanEdit' => false,
            'CanDelete' => false,
            'CanView' => false
        ];

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
