<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class group_messages extends Model
{
    use HasFactory;
    protected $fillable = ['group_id', 'user_id', 'message'];

    public function group()
    {
        return $this->belongsTo(groups::class, 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
        // return $this->belongsTo(User::class, 'sender_id');
    }
}
