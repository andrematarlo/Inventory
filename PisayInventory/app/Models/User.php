<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'UserAccount';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'UserAccountID';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Username',
        'Password',
        'role',
        'CreatedById',
        'DateCreated',
        'ModifiedById',
        'DateModified',
        'DeletedById',
        'RestoredById',
        'DateDeleted',
        'DateRestored',
        'IsDeleted',
        'name',
        'email',
        'student_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'Password',
        'remember_token',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'DateCreated' => 'datetime',
        'DateModified' => 'datetime',
        'DateDeleted' => 'datetime',
        'DateRestored' => 'datetime',
        'IsDeleted' => 'boolean'
    ];

    protected $attributes = [
        'IsDeleted' => false
    ];

    // Override the default password column name
    public function getAuthPassword()
    {
        return $this->Password;
    }

    // Add this method to handle username attribute
    public function getUsernameAttribute($value)
    {
        return $value;
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'CreatedById', 'EmployeeID');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(Employee::class, 'ModifiedById', 'EmployeeID');
    }
    

    public function role()
    {
        return $this->belongsTo(Role::class, 'RoleId', 'RoleId');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'UserAccountID', 'UserAccountID');
    }
    

    public function student()
    {
        return $this->hasOne(Student::class, 'UserAccountID', 'UserAccountID');
    }

    // Helper method to check role
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    // Helper method to check multiple roles
    public function hasAnyRole($roles)
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    public function getBalance()
    {
        if (!$this->student_id) {
            return 0;
        }

        return \App\Models\CashDeposit::where('student_id', $this->student_id)
            ->whereNull('deleted_at')
            ->sum(\DB::raw('Amount * CASE WHEN TransactionType = "DEPOSIT" THEN 1 ELSE -1 END'));
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'student_id', 'UserAccountID');
    }
}
