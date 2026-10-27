<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\SoftDeletes;

class Division extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions { return LogOptions::defaults()->logAll()->logOnlyDirty(); }

    /**
     * Get the department that owns the Division
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get all of the unit for the Division
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function unit()
    {
        return $this->hasMany(Unit::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * The documentTypes that belong to the Division
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    // public function documentType()
    // {
    //     return $this->belongsToMany(DocumentType::class, 'divisions_document_types', 'division_id', 'document_type_id');
    // }

    /**
     * The documentType that belong to the Division
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function documentType()
    {
        return $this->belongsToMany(DocumentType::class, 'divisions_document_types', 'division_id', 'document_type_id');
    }

    /**
     * Get all of the folder for the Division
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function folder()
    {
        return $this->hasMany(Folder::class);
    }

    /**
     * Get all of the users for the Division
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function designations()
    {
        return $this->hasMany(Designation::class);
    }

    /**
     * The fileType that belong to the Division
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fileType()
    {
        return $this->belongsToMany(FileType::class, 'division_file_type');
    }

    /**
     * Get all of the archive for the Department
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function archive()
    {
        return $this->hasMany(Archive::class);
    }

    /**
     * The task that belong to the Division
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function task()
    {
        return $this->belongsToMany(Task::class, 'division_tasks');
    }

     /**
     * Get all of the groupMessages for the Unit
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupMessages()
    {
        return $this->hasMany(GroupMessages::class, 'division_id', 'id');
    }

    protected static function booted(){
        static::deleted(function($division){
            $division->deleted_by = auth()->id();
            $division->save();
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
}

