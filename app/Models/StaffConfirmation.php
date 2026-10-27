<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffConfirmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'appointment_date',
        'confirmation_due_date',
        'alert_date',
        'status',
        'recommendation_notes',
        'approved_by',
        'approved_at',
        'gazette_reference',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'confirmation_due_date' => 'date',
        'alert_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
