<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\ConfidentialScope);
    }

    /**
     * Get all of the task for the Workflow
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function task()
    {
        return $this->hasMany(Task::class);
    }
    
    public function versions()
    {
        return $this->hasMany(WorkflowVersion::class, 'workflow_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function documentTemplate()
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    /**
     * Determine if workflow is available globally across all departments.
     */
    public function isGlobal(): bool
    {
        return is_null($this->department_id);
    }

    /**
     * Enforce civil service rule: Departmental workflows must be initiated by the Director.
     */
    public function canBeInitiatedBy(?User $user): bool
    {
        if (!$user) return false;
        
        if ($user->hasRole('super admin') || $user->hasRole('super user') || $user->hasRole('Accountant-General')) {
            return true;
        }

        if ($this->isGlobal()) {
            return true;
        }

        // Must belong to the department
        if ($user->department_id !== $this->department_id) {
            return false;
        }

        // Must be the Director of the Department
        $isDirectorRole = $user->hasRole('director') || $user->hasRole('Director');
        $isDeptHead = (bool)$user->is_department_head;
        $designationTitle = strtolower($user->designation?->title ?? '');
        $isDirectorDesignation = str_contains($designationTitle, 'director') && !str_contains($designationTitle, 'deputy') && !str_contains($designationTitle, 'assistant');

        return $isDirectorRole || $isDeptHead || $isDirectorDesignation;
    }
}
