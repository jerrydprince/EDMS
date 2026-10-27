<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WelfareClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'claim_type',
        'amount_requested',
        'amount_approved',
        'justification',
        'evidence_path',
        'sla_deadline',
        'status',
        'recommended_by',
        'approved_by',
    ];

    protected $casts = [
        'amount_requested' => 'float',
        'amount_approved' => 'float',
        'sla_deadline' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recommendedBy()
    {
        return $this->belongsTo(User::class, 'recommended_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
