<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowSla extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    protected $casts = [
        'is_working_hours_only' => 'boolean',
        'remove_original_assignee' => 'boolean',
        'requires_pause_approval' => 'boolean',
    ];

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }
}