<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'current_level',
        'direction',
        'status',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
