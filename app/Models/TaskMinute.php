<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskMinute extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'role',
        'action',
        'directive',
        'remark',
        'signature_path',
        'skip_reason',
        'is_signed',
        'signed_at'
    ];

    protected $casts = [
        'is_signed' => 'boolean',
        'signed_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
