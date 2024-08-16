<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mail extends Model
{
    use HasFactory;
    protected $fillable=['recipient', 'subject','body','cc','bcc', 'doc'];

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}


