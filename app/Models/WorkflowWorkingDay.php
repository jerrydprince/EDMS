<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowWorkingDay extends Model
{
    protected $fillable = [
        'day_of_week',
        'day_name',
        'is_working_day',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'is_working_day' => 'boolean',
    ];
}
