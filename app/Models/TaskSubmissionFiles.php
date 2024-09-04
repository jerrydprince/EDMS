<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskSubmissionFiles extends Model
{
    use HasFactory;

    /**
     * Get the taskSubmission that owns the TaskSubmissionFiles
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taskSubmission()
    {
        return $this->belongsTo(TaskSubmission::class);
    }
}
