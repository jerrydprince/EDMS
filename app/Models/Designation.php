<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'division_id',
        'unit_id',
        'section_id',
        'hierarchy_level',
        'title',
        'cadre',
        'grade_level',
        'description',
        'is_registry',
        'is_head_of_office',
        'is_active',
    ];

    protected $casts = [
        'is_registry' => 'boolean',
        'is_head_of_office' => 'boolean',
        'is_active' => 'boolean',
        'grade_level' => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope for active designations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for designations available at a specific hierarchy level
     */
    public function scopeForHierarchy($query, $departmentId = null, $divisionId = null, $unitId = null)
    {
        if (!$departmentId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('is_active', true)
            ->where('department_id', $departmentId)
            ->where(function ($q) use ($divisionId, $unitId) {
                if ($unitId) {
                    $q->where('unit_id', $unitId)
                      ->orWhere('division_id', $divisionId)
                      ->orWhereNull('division_id');
                } elseif ($divisionId) {
                    $q->where('division_id', $divisionId)
                      ->orWhereNull('division_id');
                } else {
                    $q->whereNull('division_id')->orWhereNotNull('department_id');
                }
            });
    }

    /**
     * Scope for registry designations
     */
    public function scopeRegistry($query)
    {
        return $query->where('is_registry', true);
    }
}
