<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatArchive extends Model
{
    protected $fillable = [
        'user_id',
        'archived_user_id',
        'group_id',
        'group_type'
    ];
}
