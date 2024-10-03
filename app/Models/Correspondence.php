<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Correspondence extends Model
{
    use HasFactory;

    /**
     * Get all of the correspondenceFile for the Correspondence
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function correspondenceFile()
    {
        return $this->hasMany(CorrespondenceFile::class);
    }

    /**
     * Get the user associated with the Correspondence
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sentBy()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
}
