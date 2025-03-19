<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deposit extends Model
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

    protected $casts = [
        'Amount' => 'decimal:2',
        'BalanceBefore' => 'decimal:2',
        'BalanceAfter' => 'decimal:2',
        'TransactionDate' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
} 