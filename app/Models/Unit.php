<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions { return LogOptions::defaults()->logAll()->logOnlyDirty(); }

    /**
     * Get the division that owns the Unit
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * The documentTypes that belong to the Unit
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function documentType()
    {
        return $this->belongsToMany(DocumentType::class, 'unit_document_types');
    }

    /**
     * Get all of the folder for the Unit
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function folder()
    {
        return $this->hasMany(Folder::class);
    }

    /**
     * Get all of the users for the Unit
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

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    /**
     * The fileType that belong to the Unit
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fileType()
    {
        return $this->belongsToMany(FileType::class, 'unit_file_type');
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
     * The task that belong to the Unit
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function task()
    {
        return $this->belongsToMany(Task::class, 'unit_tasks');
    }

    /**
     * Get all of the groupMessages for the Unit
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupMessages()
    {
        return $this->hasMany(GroupMessages::class, 'unit_id', 'id');
    }

    protected static function booted(){
        static::deleted(function($unit){
            $unit->deleted_by = auth()->id();
            $unit->save();
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


