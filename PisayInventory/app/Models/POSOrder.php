<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class POSOrder extends Model
{
    protected $table = 'pos_orders';
    protected $primaryKey = 'OrderID';
    
    protected $fillable = [
        'OrderNumber',
        'TotalAmount',
        'PaymentMethod',
        'student_id',
        'customer_name',
        'Status',
        'AmountTendered',
        'ChangeAmount',
        'ProcessedBy',
        'ProcessedAt',
        'Remarks'
    ];
    
    protected $casts = [
        'TotalAmount' => 'decimal:2',
        'AmountTendered' => 'decimal:2',
        'ChangeAmount' => 'decimal:2',
        'ProcessedAt' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    
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
     * Get the user who processed the order.
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'ProcessedBy');
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
    
    /**
     * Scope a query to only include cancelled orders.
     */
    public function scopeCancelled($query)
    {
        return $query->where('Status', 'cancelled');
    }
} 