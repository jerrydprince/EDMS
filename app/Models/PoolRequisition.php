<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoolRequisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_no',
        'mda_name',
        'mda_code',
        'letter_ref',
        'letter_date',
        'justification',
        'grade_level_required',
        'number_required',
        'special_skills_required',
        'has_letterhead',
        'has_accommodation',
        'status',
        'shortlisted_officer_ids',
        'created_by',
        'treated_by',
    ];

    protected $casts = [
        'special_skills_required' => 'array',
        'shortlisted_officer_ids' => 'array',
        'has_letterhead' => 'boolean',
        'has_accommodation' => 'boolean',
        'letter_date' => 'date',
    ];

    public function postings()
    {
        return $this->hasMany(PoolPosting::class, 'pool_requisition_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function treatedBy()
    {
        return $this->belongsTo(User::class, 'treated_by');
    }
}
