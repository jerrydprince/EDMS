<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "task";

    /**
     * Get the chat group associated with the task discussion.
     */
    public function chatGroup()
    {
        return $this->belongsTo(Groups::class, 'chat_group_id');
    }

    /**
     * The user that belong to the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function user()
    {
        return $this->belongsToMany(User::class, 'user_task');
    }

    /**
     * Get all of the taskdocs for the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taskdocs()
    {
        return $this->hasMany(TaskDoc::class);
    }

    protected $fillable = [
        'title', 'task', 'description', 'file_name', 'original_file_name', 
        'start_date', 'due_date', 'status', 'created_by', 'assigned_by',
        'is_approved', 'deleted_by', 'file_id', 'mail_id',
        'workflow_version_id', 'current_step_id', 'department_id',
        'division_id', 'unit_id', 'super_user_id', 'task_type',
        'task_for', 'assignee_id', 'task_from_id',
        'is_re_assigned',
        'allow_step_splitting',
        'form_data',
        'chat_group_id',
        'parent_task_id',
        'is_signed',
        'tracking_id',
        'folder_id',
    ];

    protected $casts = [
        'form_data' => 'array',
    ];

    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subTasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }
    /**
     * Get the assignedBy associated with the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function assignedBy()
    {
        return $this->hasOne(User::class, 'id', 'assigned_by');
    }

    /**
     * Get all of the comments for the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function assignmentComments()
    {
        return $this->hasManyThrough(TaskAssignmentComments::class, TaskAssignment::class, 'task_id', 'task_assignment_id');
    }

    /**
     * The unitTask that belong to the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function unitTask()
    {
        return $this->belongsToMany(Unit::class, 'unit_tasks');
    }

    /**
     * The divisionTask that belong to the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function divisionTask()
    {
        return $this->belongsToMany(Division::class, 'division_tasks');
    }

    /**
     * The departmentTask that belong to the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function departmentTask()
    {
        return $this->belongsToMany(Department::class, 'department_tasks');
    }

     /**
     * The super user that belong to the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function superuserTask()
    {
        return $this->belongsToMany(Department::class, 'superuser_tasks');
    }

    /**
     * Get the assignedByDivision associated with the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function assignedByDivision()
    {
        return $this->hasOne(Division::class, 'id', 'assigned_by_division');
    }

     /**
     * Get the assignedByUnit associated with the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function assignedByUnit()
    {
        return $this->hasOne(Unit::class, 'id', 'assigned_by_unit');
    }

    /**
     * Get the assignedByDepartment associated with the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function assignedByDepartment()
    {
        return $this->hasOne(Department::class, 'id', 'assigned_by_department');
    }

     /**
     * Get the assignedBySuperUser associated with the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function assignedBySuperUser()
    {
        return $this->hasOne(User::class, 'id', 'assigned_by_super_user');
    }

    /**
     * Get the taskSubmission associated with the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function taskSubmission()
    {
        return $this->hasMany(TaskSubmission::class, 'task_id');
    }

    /**
     * Get all of the rating for the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rating()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get the taskActivity associated with the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function taskActivity()
    {
        return $this->hasMany(TaskActivity::class);
    }

    protected static function booted(){
        static::deleted(function($task){
            $task->deleted_by = auth()->id();
            $task->save();
        });

        static::creating(function ($task) {
            if (empty($task->tracking_id)) {
                $task->tracking_id = 'TSK-' . strtoupper(uniqid());
            }
        });
    }

    /**
    * Get the deletedBy associated with the Archive
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
   public function deletedBy()
   {
       return $this->hasOne(User::class, 'id', 'deleted_by');
   }

   /**
    * Get all of the assignments for the Task
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
   public function assignments()
   {
       return $this->hasMany(TaskAssignment::class, 'task_id', 'id');
   }

   public function assignedOutAssignments()
   {
       return $this->hasMany(TaskAssignment::class, 'task_id', 'id')->where('assigned_by', auth()->id())->orderBy('id', 'desc');
   }

   /**
    * Get all of the assignments for the Task
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
   public function assignedToMe()
   {
       return $this->hasOne(TaskAssignment::class, 'task_id', 'id')->where('assigned_to', auth()->id())->latestOfMany();
   }

   /**
    * Get all of the taskSubmissionFiles for the Task
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
    */
   public function taskSubmissionFiles()
   {
       return $this->hasManyThrough(TaskSubmissionFiles::class, TaskAssignment::class);
   }

    public function workflowVersion()
    {
        return $this->belongsTo(WorkflowVersion::class, 'workflow_version_id');
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function mail()
    {
        return $this->belongsTo(Mail::class, 'mail_id');
    }

    /**
     * Get all of the active steps for the Task (Generic Workflow)
     */
    public function activeSteps()
    {
        return $this->hasMany(TaskActiveStep::class, 'task_id', 'id')->where('status', 'in_progress');
    }

    /**
     * Get the current active step for the Task (Generic Workflow)
     */
    public function activeStep()
    {
        return $this->hasOne(TaskActiveStep::class, 'task_id', 'id')->where('status', 'in_progress');
    }

    /**
     * Get all of the workflow logs for the Task (Generic Workflow)
     */
    public function workflowLogs()
    {
        return $this->hasMany(WorkflowLog::class, 'task_id', 'id')->orderBy('created_at', 'desc');
    }

    public function minutes()
    {
        return $this->hasMany(TaskMinute::class, 'task_id', 'id')->orderBy('created_at', 'asc');
    }

    public function documentVersions()
    {
        return $this->hasMany(TaskDocumentVersion::class, 'task_id', 'id')->orderBy('version', 'asc');
    }

    public function latestDocumentVersion()
    {
        return $this->hasOne(TaskDocumentVersion::class, 'task_id', 'id')->latestOfMany('version');
    }

    public function annotations()
    {
        return $this->hasManyThrough(TaskAnnotation::class, TaskDocumentVersion::class, 'task_id', 'version_id', 'id', 'id');
    }

    public function informationRequests()
    {
        return $this->hasMany(TaskInformationRequest::class, 'task_id', 'id');
    }

    public function archivedFolder()
    {
        return $this->belongsTo(Folder::class, 'folder_id', 'id');
    }
}
