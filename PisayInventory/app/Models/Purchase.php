<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Enums\PurchaseStatus;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;

class Purchase extends Model
{
    protected $table = 'purchase_order';
    protected $primaryKey = 'PurchaseOrderID';
    public $timestamps = false;

    protected $fillable = [
        'PONumber',
        'SupplierID',
        'OrderDate',
        'Status',
        'TotalAmount',
        'DateCreated',
        'CreatedByID',
        'ModifiedByID',
        'DateModified',
        'DeletedByID',
        'RestoredById',
        'DateDeleted',
        'DateRestored',
        'IsDeleted'
    ];

    protected $dates = [
        'OrderDate',
        'DateCreated',
        'DateModified',
        'DateDeleted',
        'DateRestored'
    ];

    protected $casts = [
        'TotalAmount' => 'decimal:2',
        'IsDeleted' => 'boolean',
        'Status' => PurchaseStatus::class,
        'OrderDate' => 'datetime',
        'DateCreated' => 'datetime',
        'DateModified' => 'datetime',
        'DateDeleted' => 'datetime',
        'DateRestored' => 'datetime'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('IsDeleted', false);
    }

    public function scopePending($query)
    {
        return $query->where('Status', PurchaseStatus::PENDING->value)
                    ->where('IsDeleted', false);
    }

    public function scopeWithCustomTrashed($query)
    {
        return $query;
    }

    public function scopeOnlyTrashed($query)
    {
        return $query->where('IsDeleted', true);
    }

    // Custom soft delete methods
    public function softDelete()
    {
        try {
            DB::beginTransaction();
            
            // Get the employee record for the authenticated user
            $userAccountId = Auth::id();
            \Log::info('Attempting to find employee for UserAccountID:', ['user_account_id' => $userAccountId]);
            
            $employee = Employee::where('UserAccountID', $userAccountId)->first();
            
            if (!$employee) {
                \Log::error('Employee not found for UserAccountID:', ['user_account_id' => $userAccountId]);
                throw new \Exception('Employee not found for UserAccountID: ' . $userAccountId);
            }
            
            \Log::info('Found employee:', ['employee_id' => $employee->EmployeeID]);

            $this->IsDeleted = true;
            $this->DeletedByID = $employee->EmployeeID;
            $this->DateDeleted = now();
            $this->ModifiedByID = $employee->EmployeeID;
            $this->DateModified = now();
            
            $result = $this->save();
            
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in softDelete:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function restore()
    {
        try {
            DB::beginTransaction();
            
            // Get the employee record for the authenticated user
            $userAccountId = Auth::id();
            \Log::info('Attempting to find employee for restore:', ['user_account_id' => $userAccountId]);
            
            $employee = Employee::where('UserAccountID', $userAccountId)->first();
            
            if (!$employee) {
                \Log::error('Employee not found for restore:', ['user_account_id' => $userAccountId]);
                throw new \Exception('Employee not found for UserAccountID: ' . $userAccountId);
            }
            
            \Log::info('Found employee for restore:', ['employee_id' => $employee->EmployeeID]);

            $this->IsDeleted = false;
            $this->DateDeleted = null;
            $this->DeletedByID = null;
            $this->RestoredById = $employee->EmployeeID;
            $this->DateRestored = now();
            $this->ModifiedByID = $employee->EmployeeID;
            $this->DateModified = now();
            
            $result = $this->save();
            
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in restore:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierID', 'SupplierID');
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'PurchaseOrderID', 'PurchaseOrderID');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'CreatedByID', 'EmployeeID', 'employee');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(Employee::class, 'ModifiedByID', 'EmployeeID', 'employee');
    }

    public function deletedBy()
    {
        return $this->belongsTo(Employee::class, 'DeletedByID', 'EmployeeID', 'employee');
    }

    public function restoredBy()
    {
        return $this->belongsTo(Employee::class, 'RestoredById', 'EmployeeID', 'employee');
    }

    // Mutators
    public function setDateCreatedAttribute($value)
    {
        $this->attributes['DateCreated'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function setDateModifiedAttribute($value)
    {
        $this->attributes['DateModified'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    // Helper methods
    public function getTotalAmount()
    {
        return $this->items->sum(function($item) {
            return $item->Quantity * $item->UnitPrice;
        });
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchase) {
            if (!$purchase->Status) {
                $purchase->Status = PurchaseStatus::PENDING->value;
            }
            
            // Get the employee record
            $employee = Employee::where('UserAccountID', Auth::id())->first();
            if ($employee) {
                $purchase->CreatedByID = $employee->EmployeeID;
            }
            $purchase->DateCreated = now();
        });

        static::updating(function ($purchase) {
            // Get the employee record
            $employee = Employee::where('UserAccountID', Auth::id())->first();
            if ($employee) {
                $purchase->ModifiedByID = $employee->EmployeeID;
            }
            $purchase->DateModified = now();
        });

        // Fix for withTrashed macro
        static::macro('withTrashed', function ($query) {
            return $query;
        });

        // Fix for Builder macro
        Builder::macro('withTrashed', function ($query) {
            return $query;
        });
    }
}
