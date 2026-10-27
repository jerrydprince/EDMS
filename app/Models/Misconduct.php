<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Misconduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket',
        'complaint',
        'file',
        'user_id',
        'reported_by',
        'status',
        'incident_date',
        'action_taken',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
