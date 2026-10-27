<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    use \App\Traits\HasEbeweWorkflow;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\DepartmentScope);
        static::addGlobalScope(new \App\Models\Scopes\ConfidentialScope);
    }
    // protected $fillable = [
    //     'name', 'file_type', 'status', 'subject', 'is_incoming',
    //     'is_outgoing','department_id', 'doc_no', 'sent_from_department_id', 'sent_to_department_id',
    //     'sent_from_division_id', 'sent_from_unit_id', 'division_id', 'unit_id',
    // ];
    protected $fillable = [
        'name', 'file_type', 'description','department_id', 'doc_no', 'division_id', 'unit_id', 'is_confidential',
    ];
    // public function movements()
    // {
    //     return $this->hasMany(FileMovement::class);
    // }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function sentFromDepartment()
    {
        return $this->belongsTo(Department::class, 'sent_from_department_id');
    }

    public function sentToDepartment()
    {
        return $this->belongsTo(Department::class, 'sent_to_department_id');
    }
    public function sentFromDivision()
    {
        return $this->belongsTo(Division::class, 'sent_from_division_id');
    }

    public function sentFromUnit()
    {
        return $this->belongsTo(Unit::class, 'sent_from_unit_id');
    }

    /**
     * Get all of the fileMovement for the File
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fileMovement()
    {
        return $this->hasMany(FileMovement::class, 'file_id', 'id');
    }

    /**
     * Get the task associated with the file
     */
    public function task()
    {
        return $this->hasOne(Task::class, 'file_id', 'id')->latestOfMany();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'file_id', 'id');
    }
}
