<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

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
        return $this->belongsToMany(documentType::class, 'departments_document_types');
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
    
}
