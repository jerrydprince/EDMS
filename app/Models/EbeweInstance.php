<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbeweInstance extends Model
{
    protected $table = 'ebewe_instances';
    protected $guarded = [];

    /**
     * Get the owning document model (Payment, Task, File, etc.)
     */
    public function document()
    {
        return $this->morphTo();
    }

    public function workflow()
    {
        return $this->belongsTo(\App\Models\Workflow::class, 'workflow_id'); // Wait, wait, is it ebewe_workflows? I will just use DB facade or make EbeweWorkflow model if needed. Let's just define document for now.
    }

    public function currentStage()
    {
        return $this->belongsTo(EbeweStage::class, 'current_stage_id');
    }

    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'current_assigned_to');
    }

    public function assignedByUser()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
