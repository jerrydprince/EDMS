<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskSubmission extends Model
{
    use HasFactory;

    /**
     * Get all of the taskSubmissionFiles for the TaskSubmission
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taskSubmissionFiles()
    {
        return $this->hasMany(TaskSubmissionFiles::class, 'task_submissions_id');
    }

    /**
     * Get the user associated with the TaskSubmission
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
