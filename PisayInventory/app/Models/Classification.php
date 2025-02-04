<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classification extends Model
{
    protected $table = 'classification';
    protected $primaryKey = 'ClassificationId';
    public $timestamps = false;

    protected $fillable = [
        'ClassificationName',
        'ParentClassificationId',
        'DateCreated',
        'CreatedById',
        'DateModified',
        'ModifiedById',
        'DateDeleted',
        'DeletedById',
        'IsDeleted'
    ];

    // Query scopes for soft delete
    public function scopeActive($query)
    {
        return $query->where('IsDeleted', false);
    }

    public function scopeTrashed($query)
    {
        return $query->where('IsDeleted', true);
    }

    // User relationships
    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'CreatedById', 'UserAccountID')
                    ->from('UserAccount')
                    ->withDefault(['Username' => 'N/A']);
    }

    public function modified_by_user()
    {
        return $this->belongsTo(User::class, 'ModifiedById', 'UserAccountID')
                    ->from('UserAccount')
                    ->withDefault(['Username' => 'N/A']);
    }

    public function deleted_by_user()
    {
        return $this->belongsTo(User::class, 'DeletedById', 'UserAccountID')
                    ->from('UserAccount')
                    ->withDefault(['Username' => 'N/A']);
    }

    // Classification relationships
    public function parent()
    {
        return $this->belongsTo(Classification::class, 'ParentClassificationId', 'ClassificationId');
    }

    public function children()
    {
        return $this->hasMany(Classification::class, 'ParentClassificationId', 'ClassificationId');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'ClassificationId', 'ClassificationId')
                    ->where('IsDeleted', false);
    }
} 