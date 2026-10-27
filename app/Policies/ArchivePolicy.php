<?php

namespace App\Policies;

use App\Models\Archive;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ArchivePolicy
{
    /**
     * Common authorization check for department.
     */
    protected function checkAccess(User $user, Archive $archive)
    {
        if ($user->hasRole('super admin') || $user->hasRole('super user')) {
            return true;
        }
        
        // Director can bypass within department
        if ($user->hasRole('director')) {
            return $user->department_id === $archive->department_id;
        }

        // Division head can access within division
        if ($user->hasRole('deputy director')) {
            return $user->division_id === $archive->division_id;
        }

        // Unit head can access within unit
        if ($user->hasRole('assistant director')) {
            return $user->unit_id === $archive->unit_id;
        }
        
        // Otherwise, strictly match department, division, and unit (if applicable)
        return $user->department_id === $archive->department_id &&
               ($archive->division_id === null || $user->division_id === $archive->division_id) &&
               ($archive->unit_id === null || $user->unit_id === $archive->unit_id);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('edms view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Archive $archive): bool
    {
        return $user->hasPermissionTo('edms view') && $this->checkAccess($user, $archive);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('edms create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Archive $archive): bool
    {
        return $user->hasPermissionTo('edms update') && $this->checkAccess($user, $archive);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Archive $archive): bool
    {
        return $user->hasPermissionTo('edms delete') && $this->checkAccess($user, $archive);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Archive $archive): bool
    {
        return $user->hasPermissionTo('edms update') && $this->checkAccess($user, $archive);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Archive $archive): bool
    {
        return $user->hasPermissionTo('edms delete') && $this->checkAccess($user, $archive);
    }
}
