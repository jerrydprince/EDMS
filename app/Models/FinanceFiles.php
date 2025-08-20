<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceFiles extends Model
{
    use HasFactory;

    /**
     * Get all of the path for the FinanceFiles
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function path()
    {
        return $this->hasMany(FinanceFilePaths::class);
    }
}
