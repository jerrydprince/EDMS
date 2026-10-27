<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class ChatMessage extends Model
{
    use HasFactory;
    protected $fillable = [
        'sender_id',
        'recipient_id',
        'message',
        'reply_to_id',
        'is_pinned',
        'is_starred',
        'is_deleted',
        'is_read',
        'read_at',
        'reactions',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'reactions' => 'array',
        'is_pinned' => 'boolean',
        'is_starred' => 'boolean',
        'is_deleted' => 'boolean',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    public function replyTo()
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to_id')->with('sender');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function attachments()
    {
        return $this->hasMany(ChatAttachment::class, 'chat_message_id');
    }
    // use HasFactory;
    // protected $fillable = ['chat_user_id', 'chat_group_id', 'message', 'parent_id','doc'];

    // public function user()
    // {
    //     return $this->belongsTo(ChatUser::class, 'chat_user_id');
    // }

    // public function group()
    // {
    //     return $this->belongsTo(ChatGroup::class, 'chat_group_id');
    // }
    // public function parent()
    // {
    //     return $this->belongsTo(ChatMessage::class, 'parent_id');
    // }
    // public function replies()
    // {
    //     return $this->hasMany(ChatMessage::class, 'parent_id');
    // }
    // public function attachments(): HasMany
    // {
    //     return $this->hasMany(NewAttachment::class, 'chat_message_id');
    // }
}
