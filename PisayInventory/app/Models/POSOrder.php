<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSOrder extends Model
{
    use HasFactory;
    
    protected $table = 'pos_orders';
    protected $primaryKey = 'OrderID';
    public $timestamps = false;
    
    protected $fillable = [
        'OrderDate',
        'TotalAmount',
        'PaymentMethod',
        'Status',
        'StudentID',
        'AmountTendered',
        'Change',
        'CompletedDate',
        'ProcessedBy',
        'Remarks'
    ];
    
    /**
     * Get the items in this order.
     */
    public function items()
    {
        return $this->hasMany(POSOrderItem::class, 'OrderID', 'OrderID');
    }
    
    /**
     * Get the student associated with this order.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'StudentID', 'StudentID');
    }
    
    /**
     * Get the user who processed this order.
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'ProcessedBy', 'id');
    }
    
    /**
     * Get the formatted order number.
     */
    public function getOrderNumberAttribute()
    {
        return str_pad($this->OrderID, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Scope a query to only include pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('Status', 'Pending');
    }
    
    /**
     * Scope a query to only include completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('Status', 'Completed');
    }
} 