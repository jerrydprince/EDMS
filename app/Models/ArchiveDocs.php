<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArchiveDocs extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['archive_keys', 'archive_values', 'archive_id'];

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
