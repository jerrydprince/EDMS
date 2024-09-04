<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    /**
     * Get all of the userDescription for the Rating
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userDescription()
    {
        return $this->hasMany(UserDescription::class);
    }
}
