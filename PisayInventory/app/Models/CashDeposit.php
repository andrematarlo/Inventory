<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashDeposit extends Model
{
    use HasFactory;
    
    protected $table = 'cash_deposits';
    protected $primaryKey = 'DepositID';
    public $timestamps = false;
    
    protected $fillable = [
        'StudentID',
        'Amount',
        'TransactionDate',
        'Description',
        'TransactionType',
        'ProcessedBy',
        'Remarks'
    ];
    
    /**
     * Get the student associated with this deposit.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'StudentID', 'StudentID');
    }
    
    /**
     * Get the user who processed this deposit.
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'ProcessedBy', 'id');
    }
    
    /**
     * Scope a query to only include deposits (positive amount).
     */
    public function scopeDeposits($query)
    {
        return $query->where('Amount', '>', 0);
    }
    
    /**
     * Scope a query to only include payments (negative amount).
     */
    public function scopePayments($query)
    {
        return $query->where('Amount', '<', 0);
    }
} 