<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalStamp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'designation_title',
        'stamp_type',
        'custom_stamp_path',
        'ink_color',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
