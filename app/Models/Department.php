<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get all of the division for the Department
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function division()
    {
        return $this->hasMany(Division::class);
    }
    
    /**
     * The documentType that belong to the Department
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function documentType()
    {
        return $this->belongsToMany(DocumentType::class, 'departments_document_types');
    }

    /**
     * Get all of the user for the Department
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user()
    {
        return $this->hasMany(User::class);
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
     * Get all of the folder for the Department
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function folder()
    {
        return $this->hasMany(Folder::class);
    }

    /**
     * The fileType that belong to the Department
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fileType()
    {
        return $this->belongsToMany(FileType::class, 'department_file_type');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }
    // Relationship to get files sent from this department
    public function sentFiles()
    {
        return $this->hasMany(File::class, 'sent_from_department_id');
    }

    // Relationship to get files sent to this department
    public function receivedFiles()
    {
        return $this->hasMany(File::class, 'sent_to_department_id');
    }

    /**
     * The task that belong to the Department
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function task()
    {
        return $this->belongsToMany(Task::class, 'department_tasks');
    }

     /**
     * Get all of the groupMessages for the Unit
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupMessages()
    {
        return $this->hasMany(GroupMessages::class, 'department_id', 'id');
    }

    protected static function booted(){
        static::deleted(function($department){
            $department->deleted_by = auth()->user()->id;
            $department->save();
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
