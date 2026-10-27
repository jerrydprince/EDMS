<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskChecklist extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function workflowChecklist()
    {
        return $this->belongsTo(WorkflowChecklist::class, 'workflow_checklist_id');
    }
    
    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}