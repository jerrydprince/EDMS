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
        return $this->hasOne(Department::class, 'id', 'assigned_by_super_user');
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
            $task->deleted_by = auth()->user()->id;
            $task->save();
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

   /**
    * Get all of the assignments for the Task
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
   public function assignedToMe()
   {
       return $this->hasMany(TaskAssignment::class, 'task_id', 'id')->where('assigned_to', auth()->user()->id);
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
}
