<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'action_by',
        'action_to',
        'action_type',
        'comments'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    public function actionTo()
    {
        return $this->belongsTo(User::class, 'action_to');
    }

    public function attachments()
    {
        return $this->hasMany(WorkflowLogAttachment::class, 'workflow_log_id');
    }
}
