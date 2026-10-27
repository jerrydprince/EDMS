<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssignmentComments extends Model
{
    use HasFactory;

    public function sentToUser()
    {
        return $this->belongsTo(User::class, 'sent_to', 'id');
    }

    public function sentByUser()
    {
        return $this->belongsTo(User::class, 'sent_by', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'sent_by', 'id');
    }

    public function taskAssignment()
    {
        return $this->belongsTo(TaskAssignment::class, 'task_assignment_id', 'id');
    }
}

