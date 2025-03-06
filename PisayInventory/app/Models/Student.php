<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'students';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'date_of_birth'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_id',
        'first_name',
        'last_name',
        'email',
        'contact_number',
        'gender',
        'date_of_birth',
        'address',
        'grade_level',
        'section',
        'status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the full name of the student.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the year and section of the student.
     *
     * @return string
     */
    public function getYearSectionAttribute()
    {
        return "{$this->grade_level} - {$this->section}";
    }

    // Query scopes
    public function scopeActive($query)
    {
        return $query->where('IsDeleted', false);
    }

    public function scopeTrashed($query)
    {
        return $query->where('IsDeleted', true);
    }

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'CreatedByID', 'EmployeeID')
            ->withDefault(['FirstName' => 'Unknown', 'LastName' => 'User']);
    }

    public function modifiedBy()
    {
        return $this->belongsTo(Employee::class, 'ModifiedByID', 'EmployeeID')
            ->withDefault(['FirstName' => 'Unknown', 'LastName' => 'User']);
    }

    public function deletedBy()
    {
        return $this->belongsTo(Employee::class, 'DeletedByID', 'EmployeeID')
            ->withDefault(['FirstName' => 'Unknown', 'LastName' => 'User']);
    }

    public function restoredBy()
    {
        return $this->belongsTo(Employee::class, 'RestoredByID', 'EmployeeID')
            ->withDefault(['FirstName' => 'Unknown', 'LastName' => 'User']);
    }

    // Helper methods
    public function getFullNameWithMiddleAttribute()
    {
        if (!empty($this->MiddleName)) {
            return "{$this->FirstName} {$this->MiddleName} {$this->LastName}";
        }
        return $this->getFullNameAttribute();
    }
}