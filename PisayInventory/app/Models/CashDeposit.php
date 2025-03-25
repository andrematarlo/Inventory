<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashDeposit extends Model
{
    use SoftDeletes;

    protected $table = 'cash_deposits';
    
    protected $fillable = [
        'student_id',
        'Amount',
        'TransactionType',
        'Description',
        'transaction_date',
        'payment_method',
        'reference_number'
    ];
    
    protected $casts = [
        'Amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];
    
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'transaction_date'
    ];
    
    /**
     * Get the student that owns the deposit.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
} 