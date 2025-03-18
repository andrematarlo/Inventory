<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashDeposit extends Model
{
    use HasFactory, SoftDeletes;
    
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

    protected $dates = [
        'TransactionDate',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'Amount' => 'decimal:2',
        'BalanceBefore' => 'decimal:2',
        'BalanceAfter' => 'decimal:2'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($deposit) {
            if (empty($deposit->ReferenceNumber)) {
                $deposit->ReferenceNumber = 'DEP-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
            
            if (empty($deposit->TransactionDate)) {
                $deposit->TransactionDate = now();
            }
        });
    }
} 