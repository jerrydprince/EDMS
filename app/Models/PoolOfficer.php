<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoolOfficer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'other_names',
        'ippis_no',
        'staff_id',
        'gender',
        'cadre',
        'grade_level',
        'step',
        'state_of_origin',
        'lga',
        'geopolitical_zone',
        'current_mda_name',
        'current_mda_code',
        'directorate_in_mda',
        'date_posted_to_mda',
        'tenancy_years',
        'special_competencies',
        'confirmation_status',
        'date_of_first_appointment',
        'date_of_present_appointment',
        'phone',
        'email',
        'status',
        'remarks',
    ];

    protected $casts = [
        'special_competencies' => 'array',
        'date_posted_to_mda' => 'date',
        'date_of_first_appointment' => 'date',
        'date_of_present_appointment' => 'date',
        'tenancy_years' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function postings()
    {
        return $this->hasMany(PoolPosting::class, 'pool_officer_id')->orderBy('effective_date', 'desc');
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->other_names} {$this->last_name}");
    }
}
