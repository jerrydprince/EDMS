<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssignmentComments extends Model
{
    use HasFactory;

    /**
     * Get the user associated with the TaskAssignmentComments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sentToUser()
    {
        return $this->hasOne(User::class, 'id', 'sent_by');
    }

    /**
     * Get the user associated with the TaskAssignmentComments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sentByUser()
    {
        return $this->hasOne(User::class, 'id', 'sent_by');
    }
}
