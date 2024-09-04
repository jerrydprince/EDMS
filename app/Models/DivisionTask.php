<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DivisionTask extends Model
{
    use HasFactory;

    /**
     * The taskt that belong to the DivisionTask
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function taskt()
    {
        return $this->belongsToMany(Task::class, 'division_task');
    }
}
