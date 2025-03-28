<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Classification;
use App\Models\Supplier;
use App\Models\Inventory;
use App\Models\Employee;
use App\Models\Unit;
use App\Models\Role;
use App\Models\RolePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }    
        
        // Keep existing counts
        $totalItems = Item::where('IsDeleted', false)->count();
        $totalEmployees = Employee::where('IsDeleted', false)->count();
        $totalSuppliers = Supplier::where('IsDeleted', false)->count();
        
        // Keep low stock items
        $lowStockItems = Item::where('IsDeleted', false)
            ->whereRaw('StocksAvailable <= ReorderPoint')
            ->get();

        $lastWeek = now()->subDays(7);

        // Updated activities collection for items
        $itemActivities = Item::with(['classification', 'created_by_user', 'modified_by_user', 'deleted_by_user', 'restored_by_user'])
            ->where(function($query) use ($lastWeek) {
                $query->where('DateCreated', '>=', $lastWeek)
                      ->orWhere('DateModified', '>=', $lastWeek)
                      ->orWhere('DateDeleted', '>=', $lastWeek)
                      ->orWhere('DateRestored', '>=', $lastWeek);
            })
            ->get()
            ->map(function($item) {
                $changes = [];
                if ($item->DateModified && $item->modified_by_user && !$item->DateRestored) {
                    $changes = $this->getModelChanges($item);
                }

                return [
                    'type' => 'item',
                    'name' => $item->ItemName,
                    'created_at' => $item->DateCreated,
                    'modified_at' => $item->DateModified,
                    'deleted_at' => $item->DateDeleted,
                    'restored_at' => $item->DateRestored,
                    'created_by' => $item->created_by_user->Username ?? 'System',
                    'modified_by' => $item->modified_by_user->Username ?? 'System',
                    'deleted_by' => $item->deleted_by_user->Username ?? 'System',
                    'restored_by' => $item->restored_by_user->Username ?? 'System',
                    'is_deleted' => $item->IsDeleted,
                    'details' => $item->classification ? "({$item->classification->ClassificationName})" : '',
                    'changes' => $changes
                ];
            });

        // Updated activities collection for suppliers
        $supplierActivities = Supplier::with(['created_by_user', 'modified_by_user', 'deleted_by_user', 'restored_by_user'])
            ->where(function($query) use ($lastWeek) {
                $query->where('DateCreated', '>=', $lastWeek)
                      ->orWhere('DateModified', '>=', $lastWeek)
                      ->orWhere('DateDeleted', '>=', $lastWeek)
                      ->orWhere('DateRestored', '>=', $lastWeek);
            })
            ->get()
            ->map(function($supplier) {
                $changes = [];
                if ($supplier->DateModified && $supplier->modified_by_user && !$supplier->DateRestored) {
                    $changes = $this->getModelChanges($supplier);
                }

                return [
                    'type' => 'supplier',
                    'name' => $supplier->SupplierName,
                    'created_at' => $supplier->DateCreated,
                    'modified_at' => $supplier->DateModified,
                    'deleted_at' => $supplier->DateDeleted,
                    'restored_at' => $supplier->DateRestored,
                    'created_by' => $supplier->created_by_user->Username ?? 'System',
                    'modified_by' => $supplier->modified_by_user->Username ?? 'System',
                    'deleted_by' => $supplier->deleted_by_user->Username ?? 'System',
                    'restored_by' => $supplier->restored_by_user->Username ?? 'System',
                    'is_deleted' => $supplier->IsDeleted,
                    'details' => $supplier->ContactNum ? "(Contact: {$supplier->ContactNum})" : '',
                    'changes' => $changes
                ];
            });

        // Updated activities collection for employees
        $employeeActivities = Employee::with(['createdBy', 'modifiedBy', 'deletedBy', 'restoredBy'])
            ->where(function($query) use ($lastWeek) {
                $query->where('DateCreated', '>=', $lastWeek)
                      ->orWhere('DateModified', '>=', $lastWeek)
                      ->orWhere('DateDeleted', '>=', $lastWeek)
                      ->orWhere('DateRestored', '>=', $lastWeek);
            })
            ->get()
            ->map(function($employee) {
                // Determine the most recent action
                $dates = [
                    'created' => strtotime($employee->DateCreated),
                    'modified' => strtotime($employee->DateModified ?? '0'),
                    'deleted' => strtotime($employee->DateDeleted ?? '0'),
                    'restored' => strtotime($employee->DateRestored ?? '0')
                ];
                
                $mostRecentAction = array_search(max($dates), $dates);
                
                // Only get changes if this is a modification
                $changes = [];
                if ($mostRecentAction === 'modified' && $employee->modifiedBy) {
                    $changes = $this->getModelChanges($employee);
                }

                // Clear DateModified if this is a new record to ensure it shows as "Added"
                $modifiedAt = $employee->DateModified;
                if ($mostRecentAction === 'created') {
                    $modifiedAt = null;
                }

                return [
                    'type' => 'employee',
                    'name' => "{$employee->FirstName} {$employee->LastName}",
                    'created_at' => $employee->DateCreated,
                    'modified_at' => $modifiedAt,
                    'deleted_at' => $employee->DateDeleted,
                    'restored_at' => $employee->DateRestored,
                    'created_by' => optional($employee->createdBy)->FirstName && optional($employee->createdBy)->LastName 
                        ? optional($employee->createdBy)->FirstName . ' ' . optional($employee->createdBy)->LastName 
                        : 'System',
                    'modified_by' => optional($employee->modifiedBy)->FirstName && optional($employee->modifiedBy)->LastName
                        ? optional($employee->modifiedBy)->FirstName . ' ' . optional($employee->modifiedBy)->LastName
                        : 'System',
                    'deleted_by' => optional($employee->deletedBy)->FirstName && optional($employee->deletedBy)->LastName
                        ? optional($employee->deletedBy)->FirstName . ' ' . optional($employee->deletedBy)->LastName
                        : 'System',
                    'restored_by' => optional($employee->restoredBy)->FirstName && optional($employee->restoredBy)->LastName
                        ? optional($employee->restoredBy)->FirstName . ' ' . optional($employee->restoredBy)->LastName
                        : 'System',
                    'is_deleted' => $employee->IsDeleted,
                    'details' => $employee->Email ? "(Email: {$employee->Email})" : '',
                    'changes' => $changes
                ];
            });

        // Updated activities collection for classifications
        $classificationActivities = Classification::with(['created_by_user', 'modified_by_user', 'deleted_by_user', 'restored_by_user'])
            ->where(function($query) use ($lastWeek) {
                $query->where('DateCreated', '>=', $lastWeek)
                      ->orWhere('DateModified', '>=', $lastWeek)
                      ->orWhere('DateDeleted', '>=', $lastWeek)
                      ->orWhere('DateRestored', '>=', $lastWeek);
            })
            ->get()
            ->map(function($classification) {
                $changes = [];
                if ($classification->DateModified && $classification->modified_by_user && !$classification->DateRestored) {
                    $changes = $this->getModelChanges($classification);
                }

                return [
                    'type' => 'classification',
                    'name' => $classification->ClassificationName,
                    'created_at' => $classification->DateCreated,
                    'modified_at' => $classification->DateModified,
                    'deleted_at' => $classification->DateDeleted,
                    'restored_at' => $classification->DateRestored,
                    'created_by' => $classification->created_by_user->Username ?? 'System',
                    'modified_by' => $classification->modified_by_user->Username ?? 'System',
                    'deleted_by' => $classification->deleted_by_user->Username ?? 'System',
                    'restored_by' => $classification->restored_by_user->Username ?? 'System',
                    'is_deleted' => $classification->IsDeleted,
                    'details' => '',
                    'changes' => $changes
                ];
            });

        // Updated activities collection for units
        $unitActivities = Unit::with(['createdBy', 'modifiedBy', 'deletedBy', 'restoredBy'])
            ->where(function($query) use ($lastWeek) {
                $query->where('DateCreated', '>=', $lastWeek)
                      ->orWhere('DateModified', '>=', $lastWeek)
                      ->orWhere('DateDeleted', '>=', $lastWeek)
                      ->orWhere('DateRestored', '>=', $lastWeek);
            })
            ->get()
            ->map(function($unit) {
                $changes = [];
                if ($unit->DateModified && $unit->modifiedBy && !$unit->DateRestored) {
                    $changes = $this->getModelChanges($unit);
                }

                return [
                    'type' => 'unit',
                    'name' => $unit->UnitName,
                    'created_at' => $unit->DateCreated,
                    'modified_at' => $unit->DateModified,
                    'deleted_at' => $unit->DateDeleted,
                    'restored_at' => $unit->DateRestored,
                    'created_by' => optional($unit->createdBy)->Username ?? 'System',
                    'modified_by' => optional($unit->modifiedBy)->Username ?? 'System',
                    'deleted_by' => optional($unit->deletedBy)->Username ?? 'System',
                    'restored_by' => optional($unit->restoredBy)->Username ?? 'System',
                    'is_deleted' => $unit->IsDeleted,
                    'details' => '',
                    'changes' => $changes
                ];
            });

        // Updated activities collection for inventory
        $inventoryActivities = Inventory::with(['created_by_user', 'modified_by_user', 'deleted_by_user', 'restored_by_user', 'item'])
            ->where(function($query) use ($lastWeek) {
                $query->where('DateCreated', '>=', $lastWeek)
                      ->orWhere('DateModified', '>=', $lastWeek)
                      ->orWhere('DateDeleted', '>=', $lastWeek)
                      ->orWhere('DateRestored', '>=', $lastWeek);
            })
            ->get()
            ->map(function($inventory) {
                $changes = [];
                if ($inventory->DateModified && $inventory->modified_by_user && !$inventory->DateRestored) {
                    $changes = $this->getModelChanges($inventory);
                }

                return [
                    'type' => 'inventory',
                    'name' => $inventory->item->ItemName ?? 'Unknown Item',
                    'created_at' => $inventory->DateCreated,
                    'modified_at' => $inventory->DateModified,
                    'deleted_at' => $inventory->DateDeleted,
                    'restored_at' => $inventory->DateRestored,
                    'created_by' => $inventory->created_by_user->Username ?? 'System',
                    'modified_by' => $inventory->modified_by_user->Username ?? 'System',
                    'deleted_by' => $inventory->deleted_by_user->Username ?? 'System',
                    'restored_by' => $inventory->restored_by_user->Username ?? 'System',
                    'is_deleted' => $inventory->IsDeleted,
                    'changes' => $changes
                ];
            });

        // Updated activities collection for roles
        $roleActivities = Role::with(['created_by_user', 'modified_by_user', 'deleted_by_user', 'restored_by_user'])
            ->where(function($query) use ($lastWeek) {
                $query->where('DateCreated', '>=', $lastWeek)
                      ->orWhere('DateModified', '>=', $lastWeek)
                      ->orWhere('DateDeleted', '>=', $lastWeek)
                      ->orWhere('DateRestored', '>=', $lastWeek);
            })
            ->get()
            ->map(function($role) {
                $changes = [];
                if ($role->DateModified && $role->modified_by_user && !$role->DateRestored) {
                    $changes = $this->getModelChanges($role);
                }

                $details = '';
                if (!empty($role->DateCreated) && 
                    empty($role->DateModified) && 
                    empty($role->DateDeleted) &&
                    empty($role->DateRestored)) {
                    $details = 'Permissions need to be configured';
                }

                return [
                    'type' => 'role',
                    'name' => $role->RoleName,
                    'created_at' => $role->DateCreated,
                    'modified_at' => $role->DateModified,
                    'deleted_at' => $role->DateDeleted,
                    'restored_at' => $role->DateRestored,
                    'created_by' => $role->created_by_user->Username ?? 'System',
                    'modified_by' => $role->modified_by_user->Username ?? 'System',
                    'deleted_by' => $role->deleted_by_user->Username ?? 'System',
                    'restored_by' => $role->restored_by_user->Username ?? 'System',
                    'is_deleted' => $role->IsDeleted,
                    'details' => $details,
                    'changes' => $changes
                ];
            });

        // Updated activities collection for role policies
        $rolePolicyActivities = RolePolicy::with(['created_by_user', 'modified_by_user', 'deleted_by_user', 'restored_by_user', 'role'])
            ->where(function($query) use ($lastWeek) {
                $query->where('DateCreated', '>=', $lastWeek)
                      ->orWhere('DateModified', '>=', $lastWeek)
                      ->orWhere('DateDeleted', '>=', $lastWeek)
                      ->orWhere('DateRestored', '>=', $lastWeek);
            })
            ->whereNotExists(function($query) {
                $query->from('roles')
                      ->whereRaw('roles.DateCreated = role_policies.DateCreated')
                      ->whereRaw('roles.RoleId = role_policies.RoleId');
            })
            ->get()
            ->map(function($policy) {
                $changes = [];
                if ($policy->DateModified && $policy->modified_by_user && !$policy->DateRestored) {
                    $changes = $this->getModelChanges($policy);
                }

                $roleName = $policy->role ? $policy->role->RoleName : 'Unknown Role';

                return [
                    'type' => 'role_policy',
                    'name' => "Modified permissions for {$roleName}",
                    'created_at' => $policy->DateCreated,
                    'modified_at' => $policy->DateModified,
                    'deleted_at' => $policy->DateDeleted,
                    'restored_at' => $policy->DateRestored,
                    'created_by' => $policy->created_by_user->Username ?? 'System',
                    'modified_by' => $policy->modified_by_user->Username ?? 'System',
                    'deleted_by' => $policy->deleted_by_user->Username ?? 'System',
                    'restored_by' => $policy->restored_by_user->Username ?? 'System',
                    'is_deleted' => $policy->IsDeleted,
                    'details' => 'Please see Role Policies for more details.',
                    'changes' => $changes
                ];
            });

        // Merge and sort activities
        $recentActivities = collect()
            ->concat($itemActivities)
            ->concat($supplierActivities)
            ->concat($employeeActivities)
            ->concat($classificationActivities)
            ->concat($unitActivities)
            ->concat($inventoryActivities)
            ->concat($roleActivities)
            ->concat($rolePolicyActivities)
            ->sortByDesc(function ($activity) {
                return max(
                    strtotime($activity['created_at'] ?? 0),
                    strtotime($activity['modified_at'] ?? 0),
                    strtotime($activity['deleted_at'] ?? 0),
                    strtotime($activity['restored_at'] ?? 0)  // Added restored_at to max comparison
                );
            })
            ->take(100);

        return view('dashboard.index', compact(
            'totalItems',
            'totalEmployees',
            'totalSuppliers',
            'lowStockItems',
            'recentActivities'
        ));
    }

    private function getModelChanges($model)
    {
        $changes = [];
        $dirty = $model->getDirty();
        
        foreach ($dirty as $field => $newValue) {
            if (!in_array($field, ['DateModified', 'ModifiedById', 'DateCreated', 'CreatedById', 
                                 'DateDeleted', 'DeletedById', 'DateRestored', 'RestoredById'])) {
                $oldValue = $model->getOriginal($field);
                if ($oldValue !== $newValue) {
                    $changes[$field] = [
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            }
        }
        
        return $changes;
    }

    private function getActionType($type)
    {
        return match ($type) {
            'IN' => 'Stock In',
            'OUT' => 'Stock Out',
            default => 'Unknown'
        };
    }

    private function getActionColor($type)
    {
        return match ($type) {
            'IN' => 'success',
            'OUT' => 'danger',
            default => 'secondary'
        };
    }
}