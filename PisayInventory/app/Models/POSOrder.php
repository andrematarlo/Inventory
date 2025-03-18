<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class POSOrder extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'pos_orders';
    protected $primaryKey = 'OrderID';
    public $timestamps = false;
    
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