<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Common authorization check for department.
     */
    protected function checkAccess(User $user, Task $task)
    {
        $userRoles = $user->roles->pluck('name')->map(fn($role) => strtolower($role))->toArray();
        if (in_array('super admin', $userRoles) || in_array('super user', $userRoles) || in_array('super-admin', $userRoles)) {
            return true;
        }

        // Creator can always access
        if ($task->created_by === $user->id) {
            return true;
        }

        // Check explicit assignments (assigned to or assigned by)
        $isAssigned = \App\Models\TaskAssignment::where('task_id', $task->id)
            ->where(function($query) use ($user) {
                $query->where('assigned_to', $user->id)
                      ->orWhere('assigned_by', $user->id);
            })
            ->exists();
        if ($isAssigned) {
            return true;
        }

        // Check if user is part of the workflow history (actor or recipient)
        if (\Schema::hasTable('workflow_logs')) {
            $isWorkflowActor = \DB::table('workflow_logs')->where('task_id', $task->id)
                ->where(function($query) use ($user) {
                    $query->where('action_by', $user->id)
                          ->orWhere('action_to', $user->id);
                })
                ->exists();
            if ($isWorkflowActor) {
                return true;
            }
        }

        // Directors can access tasks created by or assigned to their department
        if ($user->hasRole('director')) {
            if ($task->assigned_by_department === $user->department_id) {
                return true;
            }
            $departmentAssigned = \App\Models\TaskAssignment::where('task_id', $task->id)
                ->whereHas('assignedTo', function($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                })->exists();
            if ($departmentAssigned) {
                return true;
            }
        }

        // Division heads can access tasks created by or assigned to their division
        if ($user->hasRole('deputy director')) {
            if ($task->assigned_by_division === $user->division_id) {
                return true;
            }
            $divisionAssigned = \App\Models\TaskAssignment::where('task_id', $task->id)
                ->whereHas('assignedTo', function($q) use ($user) {
                    $q->where('division_id', $user->division_id);
                })->exists();
            if ($divisionAssigned) {
                return true;
            }
        }

        // Unit heads can access tasks created by or assigned to their unit
        if ($user->hasRole('assistant director')) {
            if ($task->assigned_by_unit === $user->unit_id) {
                return true;
            }
            $unitAssigned = \App\Models\TaskAssignment::where('task_id', $task->id)
                ->whereHas('assignedTo', function($q) use ($user) {
                    $q->where('unit_id', $user->unit_id);
                })->exists();
            if ($unitAssigned) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return $this->checkAccess($user, $task);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return $this->checkAccess($user, $task);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $this->checkAccess($user, $task);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $this->checkAccess($user, $task);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $this->checkAccess($user, $task);
    }
}
