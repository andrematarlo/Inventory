<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employee';
    protected $primaryKey = 'EmployeeID';
    public $timestamps = false;
    public $incrementing = true;

    // Remove automatic eager loading as it conflicts with our optimized queries
    // protected $with = ['userAccount', 'createdBy', 'modifiedBy'];

    protected $fillable = [
        'EmployeeID',
        'FirstName',
        'MiddleName',
        'LastName',
        'Email',
        'ContactNumber',
        'Department',
        'Position',
        'UserAccountID',
        'Gender',
        'Address',
        'Role',
        'IsDeleted',
        'DateCreated',
        'CreatedByID',
        'ModifiedByID',
        'DateModified',
        'DeletedByID',
        'DateDeleted',
        'RestoredById',
        'DateRestored',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'DateCreated' => 'datetime',
        'DateModified' => 'datetime',
        'DateDeleted' => 'datetime',
        'DateRestored' => 'datetime',
        'IsDeleted' => 'boolean'
    ];

    // Define relationships
    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'UserAccountID', 'UserAccountID')
            ->withDefault([
                'Username' => 'N/A',
                'role' => 'No Role'
            ]);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'employee_roles', 'EmployeeId', 'RoleId');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'CreatedByID', 'EmployeeID')
            ->withDefault([
                'FirstName' => 'System',
                'LastName' => 'User'
            ]);
    }

    public function modifiedBy()
    {
        return $this->belongsTo(Employee::class, 'ModifiedByID', 'EmployeeID')
            ->withDefault([
                'FirstName' => 'System',
                'LastName' => 'User'
            ]);
    }

    public function deletedBy()
    {
        return $this->belongsTo(Employee::class, 'DeletedByID', 'EmployeeID')
            ->withDefault([
                'FirstName' => 'System',
                'LastName' => ''
            ]);
    }

    public function restoredBy()
    {
        return $this->belongsTo(Employee::class, 'RestoredById', 'EmployeeID')
            ->withDefault([
                'FirstName' => 'System',
                'LastName' => ''
            ]);
    }

    // Get full name attribute
    public function getFullNameAttribute()
    {
        return trim("{$this->FirstName} {$this->MiddleName} {$this->LastName}");
    }

    // Helper method to get full name of creator
    public function getCreatedByNameAttribute()
    {
        // Debug the values
        Log::info('CreatedByName Debug:', [
            'employee_id' => $this->EmployeeID,
            'created_by_id' => $this->CreatedByID,
            'created_by' => $this->createdBy
        ]);

        if (!$this->CreatedByID) {
            return 'System User';
        }

        $creator = $this->createdBy;
        if (!$creator) {
            return 'System User';
        }

        return "{$creator->FirstName} {$creator->LastName}";
    }

    // Helper method to get full name of modifier
    public function getModifiedByNameAttribute()
    {
        // Get raw values from database
        \Log::info('Raw ModifiedBy Values:', [
            'employee_id' => $this->EmployeeID,
            'modified_by_id' => $this->attributes['ModifiedByID'] ?? null,
            'raw_attributes' => array_intersect_key($this->attributes, array_flip(['CreatedByID', 'ModifiedByID']))
        ]);
        
        $modifier = $this->modifiedBy;
        return $modifier ? "{$modifier->FirstName} {$modifier->LastName}" : 'Unknown User';
    }

    // Helper method to get role names as string
    public function getRoleNamesAttribute()
    {
        return $this->roles->pluck('RoleName')->implode(', ');
    }

    // Add this relationship if it doesn't exist
    public function user()
    {
        return $this->belongsTo(User::class, 'UserAccountID', 'UserAccountID');
    }

    // Add this scope to help with debugging
    public function scopeWithUserAccount($query)
    {
        return $query->leftJoin('UserAccount', 'employee.UserAccountID', '=', 'UserAccount.UserAccountID')
                     ->select('employee.*', 'UserAccount.Username');
    }

    public function getRoleAttribute()
    {
        // Get roles from the relationship
        $roles = $this->roles->pluck('RoleName');
        return $roles->isNotEmpty() ? $roles->implode(', ') : 'No Role Assigned';
    }

    // Add method to get merged permissions
    public function getMergedPermissions()
    {
        $mergedPermissions = [];
        
        // Get all active roles of the employee
        $roles = $this->roles()
            ->with(['policies' => function($query) {
                $query->where('role_policies.IsDeleted', false);
            }])
            ->get();
        
        foreach ($roles as $role) {
            // Get active policies for each role
            $policies = $role->policies;
                                
            foreach ($policies as $policy) {
                $module = $policy->Module;
                
                // Initialize module permissions if not exists
                if (!isset($mergedPermissions[$module])) {
                    $mergedPermissions[$module] = [
                        'CanView' => false,
                        'CanAdd' => false,
                        'CanEdit' => false,
                        'CanDelete' => false
                    ];
                }
                
                // Merge permissions using OR operation
                $mergedPermissions[$module]['CanView'] |= $policy->CanView;
                $mergedPermissions[$module]['CanAdd'] |= $policy->CanAdd;
                $mergedPermissions[$module]['CanEdit'] |= $policy->CanEdit;
                $mergedPermissions[$module]['CanDelete'] |= $policy->CanDelete;
            }
        }
        
        return $mergedPermissions;
    }

    /**
     * Get the user who created this employee.
     */
    public function creator()
    {
        return $this->belongsTo(UserAccount::class, 'created_by', 'UserAccountID');
    }

    /**
     * Get the user who last modified this employee.
     */
    public function modifier()
    {
        return $this->belongsTo(UserAccount::class, 'updated_by', 'UserAccountID');
    }

    /**
     * Get the user who deleted this employee.
     */
    public function deleter()
    {
        return $this->belongsTo(UserAccount::class, 'deleted_by', 'UserAccountID');
    }

    // Scope for active records
    public function scopeActive($query)
    {
        return $query->where('IsDeleted', false);
    }

    // Scope for deleted records
    public function scopeDeleted($query)
    {
        return $query->where('IsDeleted', true);
    }

    // Equipment borrowings relationship
    public function equipmentBorrowings()
    {
        return $this->hasMany(EquipmentBorrowing::class, 'borrower_id', 'EmployeeID');
    }

    // Created borrowings relationship
    public function createdBorrowings()
    {
        return $this->hasMany(EquipmentBorrowing::class, 'created_by', 'EmployeeID');
    }

    // Updated borrowings relationship
    public function updatedBorrowings()
    {
        return $this->hasMany(EquipmentBorrowing::class, 'updated_by', 'EmployeeID');
    }
} 