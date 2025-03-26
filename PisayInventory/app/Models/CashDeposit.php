<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashDeposit extends Model
{
    use SoftDeletes;

    protected $table = 'student_deposits';
    
    protected $primaryKey = 'DepositID';
    
    protected $fillable = [
        'student_id',
        'TransactionDate',
        'ReferenceNumber',
        'TransactionType',
        'Amount',
        'BalanceBefore',
        'BalanceAfter',
        'Notes'
    ];
    
    protected $casts = [
        'Amount' => 'decimal:2',
        'BalanceBefore' => 'decimal:2',
        'BalanceAfter' => 'decimal:2',
        'TransactionDate' => 'datetime',
    ];
    
    protected $dates = [
        'TransactionDate',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    /**
     * Get the student that owns the deposit.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
} 