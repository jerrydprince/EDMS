<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchiveDocs extends Model
{
    use HasFactory;

    protected $fillable = ['archive_keys', 'archive_values'];

    /**
     * Get the archive that owns the ArchiveDocs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function archive()
    {
        return $this->belongsTo(Archive::class);
    }
}
