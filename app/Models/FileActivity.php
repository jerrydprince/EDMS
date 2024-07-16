<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileActivity extends Model
{
    use HasFactory;

    protected $table = 'file_activity';

    protected $fillable = ['archive_id', 'details', 'date'];

    /**
     * Get the archive that owns the FileActivity
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function archive()
    {
        return $this->belongsTo(Archive::class);
    }
}
