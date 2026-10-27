<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function version()
    {
        return $this->belongsTo(WorkflowVersion::class, 'workflow_version_id');
    }

    public function slas()
    {
        return $this->hasMany(WorkflowSla::class, 'workflow_step_id');
    }

    public function checklists()
    {
        return $this->hasMany(WorkflowChecklist::class, 'workflow_step_id');
    }

    public function transitions()
    {
        return $this->hasMany(WorkflowTransition::class, 'from_step_id');
    }
}