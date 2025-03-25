<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class POSTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_number',
        'customer_name',
        'total_amount',
        'payment_method',
        'user_id',
        'transaction_date',
        'notes'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'total_amount' => 'decimal:2'
    ];

    /**
     * Get the user who created this transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in this transaction.
     */
    public function items()
    {
        return $this->hasMany(POSTransactionItem::class, 'transaction_id');
    }
} 