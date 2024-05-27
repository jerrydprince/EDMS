<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubFolder extends Model
{
    use HasFactory;

    /**
     * Get the folder that owns the SubFolder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
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
}
