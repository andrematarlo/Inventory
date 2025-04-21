<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class LaboratoryReagentRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'control_no',
        'school_year',
        'grade_section',
        'number_of_students',
        'subject',
        'concurrent_topic',
        'unit',
        'teacher_in_charge',
        'venue',
        'inclusive_dates',
        'inclusive_time',
        'student_names',
        'received_by',
        'date_received',
        'endorsed_by',
        'approved_by',
        'status'
    ];

    protected $casts = [
        'date_received' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->control_no = static::generateControlNumber();
        });
    }

    public static function generateControlNumber()
    {
        $year = Carbon::now()->format('Y');
        $prefix = 'LAB-REG-' . $year . '-';
        
        // Get the last used number for this year
        $lastRecord = static::where('control_no', 'like', $prefix . '%')
            ->orderBy('control_no', 'desc')
            ->first();

        if ($lastRecord) {
            // Extract the numeric part and increment
            $lastNumber = intval(substr($lastRecord->control_no, -5));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Format with leading zeros to 5 digits
        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function items()
    {
        return $this->hasMany(LaboratoryReagentItem::class);
    }
} 