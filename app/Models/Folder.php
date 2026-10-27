<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;

    protected $table = 'folder';

    protected $guarded = [];

    /**
     * Get the associated task by trace_id (tracking_id)
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'trace_id', 'tracking_id');
    }

    /**
     * Get the department that owns the Folder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the unit that owns the Folder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the division that owns the Folder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }
    
    /**
     * Get the fileType associated with the Folder
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function fileType()
    {
        return $this->hasOne(FileType::class,'id', 'file_type_id');
    }

    /**
     * Get all of the subfolder for the Folder
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subfolder()
    {
        return $this->hasMany(SubFolder::class);
    }
    
    /**
     * Get all of the archives for the Folder
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function archives()
    {
        return $this->hasMany(Archive::class, 'folder_id', 'id');
    }
    
}
