<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Archive extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the department that owns the Archive
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the division that owns the Archive
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get the unit that owns the Archive
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

 /**
  * Get all of the archiveDocs for the Archive
  *
  * @return \Illuminate\Database\Eloquent\Relations\HasMany
  */
 public function archiveDocs()
 {
     return $this->hasMany(ArchiveDocs::class);
 }


   /**
    * The filePath that belong to the Archive
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
   public function filePath()
   {
       return $this->belongsToMany(FilePath::class, 'archive_file_path');
   }

   /**
    * Get all of the fileActivity for the Archive
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
   public function fileActivity()
   {
       return $this->hasMany(FileActivity::class);
   }

   /**
    * Get all of the versions for the Archive
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
   public function version()
   {
       return $this->hasMany(Version::class);
   }
    
}
