<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedById', 'UserAccountID');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedById', 'UserAccountID');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'DeletedById', 'UserAccountID');
    }
} 