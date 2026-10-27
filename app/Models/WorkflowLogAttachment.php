<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowLogAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_log_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size'
    ];

    public function workflowLog()
    {
        return $this->belongsTo(WorkflowLog::class, 'workflow_log_id');
    }
}
