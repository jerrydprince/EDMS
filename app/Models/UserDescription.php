<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDescription extends Model
{
    use HasFactory;

    /**
     * Get the rating that owns the UserDescription
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rating(): BelongsTo
    {
        return $this->belongsTo(Rating::class);
    }
}
