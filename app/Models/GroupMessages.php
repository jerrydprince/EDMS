<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMessages extends Model
{
    use HasFactory;
    protected $fillable = ['group_id', 'user_id', 'message', 'group_type','unit_id', 'division_id', 'department_id'];

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
