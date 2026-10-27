<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AperSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'grade_level',
        'reporting_officer_id',
        'countersigning_officer_id',
        'overall_score',
        'rating_category',
        'recommendation',
        'is_checklist_verified',
        'form_path',
        'status',
    ];

    protected $casts = [
        'overall_score' => 'float',
        'is_checklist_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reportingOfficer()
    {
        return $this->belongsTo(User::class, 'reporting_officer_id');
    }

    public function countersigningOfficer()
    {
        return $this->belongsTo(User::class, 'countersigning_officer_id');
    }
}
