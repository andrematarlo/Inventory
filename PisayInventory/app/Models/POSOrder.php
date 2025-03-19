<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class POSOrder extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'pos_orders';
    protected $primaryKey = 'OrderID';
    public $timestamps = true;
    
    protected $fillable = [
        'student_id',
        'TotalAmount',
        'PaymentMethod',
        'Status',
        'OrderNumber'
    ];
    
    protected $casts = [
        'TotalAmount' => 'decimal:2',
        'deleted_at' => 'datetime'
    ];
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        // Add a global scope to handle legacy queries referencing IsDeleted
        static::addGlobalScope('removeIsDeletedReferences', function (Builder $builder) {
            $query = $builder->getQuery();
            
            // If this query has wheres, check for IsDeleted
            if (isset($query->wheres)) {
                $indexesToRemove = [];
                
                // Find any where clauses using IsDeleted
                foreach ($query->wheres as $index => $where) {
                    if (isset($where['column']) && $where['column'] === 'IsDeleted') {
                        $indexesToRemove[] = $index;
                    }
                }
                
                // Remove the IsDeleted where clauses
                foreach ($indexesToRemove as $index) {
                    unset($query->wheres[$index]);
                }
                
                // Re-index the array if we removed any elements
                if (!empty($indexesToRemove)) {
                    $query->wheres = array_values($query->wheres);
                    
                    // Also need to adjust bindings if any were removed
                    if (isset($query->bindings['where'])) {
                        foreach ($indexesToRemove as $index) {
                            if (isset($query->bindings['where'][$index])) {
                                unset($query->bindings['where'][$index]);
                            }
                        }
                        $query->bindings['where'] = array_values($query->bindings['where']);
                    }
                }
            }
        });
    }
    
    /**
     * Get the student that owns the order.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
    
    /**
     * Get the items for the order.
     */
    public function items()
    {
        return $this->hasMany(POSOrderItem::class, 'OrderID', 'OrderID');
    }
    
    /**
     * Get the user who processed this order.
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'ProcessedBy', 'id');
    }
    
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            // Generate order number if not set
            if (empty($order->OrderNumber)) {
                $order->OrderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
    
    /**
     * Scope a query to only include pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('Status', 'pending');
    }
    
    /**
     * Scope a query to only include completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('Status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('Status', 'cancelled');
    }

    public function getTotalItemsAttribute()
    {
        return $this->items()->sum('Quantity');
    }
} 