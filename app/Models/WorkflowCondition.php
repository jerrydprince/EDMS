<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowCondition extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function transition()
    {
        return $this->belongsTo(WorkflowTransition::class, 'workflow_transition_id');
    }
}