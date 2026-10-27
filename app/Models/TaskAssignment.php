<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'assigned_to',
        'assigned_by',
        'status',
        'description',
        'due_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the assignedTo that owns the TaskAssignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }

    /**
     * Get the task that owns the TaskAssignment
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }

    /**
     * Get the assignedBy that owns the TaskAssignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by', 'id');
    }

    /**
     * Get all of the taskSubmissions for the TaskAssignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taskSubmissions()
    {
        return $this->hasMany(TaskSubmission::class, 'task_assignment_id', 'id');
    }

    public function task_submissions()
    {
        return $this->hasMany(TaskSubmission::class, 'task_assignment_id', 'id');
    }

    /**
     * Get all of the taskSubmissionFiles for the TaskAssignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taskSubmissionFiles()
    {
        return $this->hasMany(TaskSubmissionFiles::class, 'task_assignment_id', 'id');
    }

    /**
     * Get all of the comments for the TaskAssignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(TaskAssignmentComments::class, 'task_assignment_id', 'id');
    }

    /**
     * Get the rating associated with the TaskAssignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function rating()
    {
        return $this->hasOne(Rating::class, 'task_assignment_id', 'id');
    }
}
