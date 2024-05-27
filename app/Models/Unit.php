<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

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
}
