<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMessages extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id', 'user_id', 'message', 'group_type', 'unit_id', 'division_id', 'department_id', 'reply_to_id',
        'is_pinned', 'is_starred', 'is_deleted', 'reactions', 'is_edited', 'edited_at'
    ];

    protected $casts = [
        'reactions' => 'array',
        'is_pinned' => 'boolean',
        'is_starred' => 'boolean',
        'is_deleted' => 'boolean',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    public function replyTo()
    {
        return $this->belongsTo(GroupMessages::class, 'reply_to_id')->with('user');
    }

    public function group()
    {
        return $this->belongsTo(groups::class, 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
        // return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments()
    {
        return $this->hasMany(ChatAttachment::class, 'group_message_id');
    }
}
