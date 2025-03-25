<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'pos_orders';
    protected $primaryKey = 'OrderID';
    
    protected $fillable = [
        'student_id',
        'total_amount',
        'payment_type',
        'amount_tendered',
        'change_amount',
        'status',
        'created_by'
    ];

    public function items()
    {
        return $this->belongsToMany(MenuItem::class, 'pos_order_items', 
                                  'OrderID', 'ItemID')
                    ->withPivot('Quantity', 'UnitPrice')
                    ->withoutGlobalScopes();
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
} 