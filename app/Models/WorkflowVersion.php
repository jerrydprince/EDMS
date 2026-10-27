<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowVersion extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'workflow_id',
        'version_number',
        'is_published',
        'published_at',
        'is_approved',
        'approved_by_id',
        'approved_at',
        'form_schema',
        'mandate_parallel_splits',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'form_schema' => 'array',
        'mandate_parallel_splits' => 'boolean',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function steps()
    {
        return $this->hasMany(WorkflowStep::class, 'workflow_version_id');
    }

    public function transitions()
    {
        return $this->hasMany(WorkflowTransition::class, 'workflow_version_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'workflow_version_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }
}