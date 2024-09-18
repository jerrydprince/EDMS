<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groups extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'category', // 'department', 'unit', or 'division'
    ];
    public function category()
    {
        return $this->belongsTo(categories::class, 'category_id');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_users', 'group_id', 'user_id' );
    }

    public function messages()
    {
        return $this->hasMany(GroupMessages::class,  'group_id');
    }
}
