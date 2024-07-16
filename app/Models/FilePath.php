<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilePath extends Model
{
    use HasFactory;

   /**
    * The archive that belong to the FilePath
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
   public function archive()
   {
       return $this->belongsToMany(Archive::class, 'archive_file_path');
   }

   protected $fillable = ['path', 'file_name', 'file_size', 'updated_by', 'updated_by_name', 'restored_by_name'];
}
