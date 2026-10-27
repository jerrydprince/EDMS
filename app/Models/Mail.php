<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mail extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable=['recipient', 'subject','body','cc','bcc', 'doc'];

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Get the user that owns the Mail
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(){
        static::deleted(function($mail){
            $mail->deleted_by = auth()->id();
            $mail->save();
        });
    }

    /**
    * Get the deletedBy associated with the Archive
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
   public function deletedBy()
   {
       return $this->hasOne(User::class, 'id', 'deleted_by');
   }
}


