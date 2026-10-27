<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_date',
        'end_date',
        'leave_type',
        'status',
        'reason',
        'user_id',
        'relief_user_id',
        'destination',
        'duty_memo_ref',
        'approved_by',
        'supervisor_comment'
    ];

    /**
     * Get the user that owns the Leave
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the designated relief officer
     */
    public function reliefUser()
    {
        return $this->belongsTo(User::class, 'relief_user_id');
    }

    /**
     * Get the supervisor/admin who approved the leave
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
