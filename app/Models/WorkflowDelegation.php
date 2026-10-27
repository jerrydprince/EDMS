<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowDelegation extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function delegator()
    {
        return $this->belongsTo(User::class, 'delegator_user_id');
    }
    
    public function delegatee()
    {
        return $this->belongsTo(User::class, 'delegatee_user_id');
    }
}