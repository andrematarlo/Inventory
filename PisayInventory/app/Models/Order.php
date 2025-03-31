<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'pos_orders';
    protected $primaryKey = 'OrderID';
    
    protected $fillable = [
        'OrderNumber',
        'TotalAmount',
        'PaymentMethod',
        'AmountTendered',
        'ChangeAmount',
        'Status',
        'student_id',
        'customer_name',
        'ProcessedBy',
        'ProcessedAt',
        'Remarks'
    ];

    protected $casts = [
        'TotalAmount' => 'decimal:2',
        'AmountTendered' => 'decimal:2',
        'ChangeAmount' => 'decimal:2',
        'ProcessedAt' => 'datetime',
        'Status' => 'string',
        'student_id' => 'string'
    ];

    protected $attributes = [
        'Status' => 'pending'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY = 'ready';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public static function getValidStatuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PAID,
            self::STATUS_PREPARING,
            self::STATUS_READY,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'OrderID', 'OrderID');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'ProcessedBy', 'UserAccountID');
    }
} 