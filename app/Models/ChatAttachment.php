<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatAttachment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'chat_message_id',
        'group_message_id',
        'file_path',
        'file_name',
        'file_type',
        'edms_file_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }

    public function edmsFile()
    {
        return $this->belongsTo(File::class, 'edms_file_id');
    }
}
