<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskActiveStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'step_id',
        'workflow_transition_id',
        'status',
        'due_date',
        'is_paused',
        'paused_at',
        'total_paused_minutes',
        'is_breached',
        'breached_at',
        'warning_sent_at',
        'pause_requested',
        'pause_approved_by'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'is_paused' => 'boolean',
        'paused_at' => 'datetime',
        'is_breached' => 'boolean',
        'breached_at' => 'datetime',
        'warning_sent_at' => 'datetime',
        'pause_requested' => 'boolean',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'step_id');
    }
}
