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
        'student_id',
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

    protected $attributes = [
        'Status' => 'pending'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'OrderID', 'OrderID');
    }

    public function customer()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'ProcessedBy', 'UserAccountID');
    }
} 