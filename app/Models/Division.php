<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

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

    /**
     * The documentTypes that belong to the Division
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function documentType()
    {
        return $this->belongsToMany(DocumentType::class, 'divisions_document_types');
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
}
