<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Department;

class RoutingService
{
    /**
     * Resolve smart route suggestion for the current user and task.
     * For Unit Staff: Suggests submission to Assistant Director.
     * For Supervisors: Suggests routing to Department Director / Deputy Director.
     *
     * @param Task $task
     * @param User|null $user
     * @return array|null
     */
    public static function resolveSmartRoutingForUser(Task $task, ?User $user = null): ?array
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return self::getSuggestedDirectorForTask($task);
        }

        if ($task->workflow_version_id) {
            return null;
        }

        $isUnitStaff = $user->hasRole('regular user') || $user->hasRole('regular users')
            || (!empty($user->unit_id) && !$user->hasAnyRole(['assistant director', 'deputy director', 'director', 'super user', 'admin', 'super admin']));

        if ($isUnitStaff) {
            // Find the upward supervisor (Assistant Director of unit/division or parent assigner)
            $supervisor = self::getUpwardSupervisor($user, $task);
            if ($supervisor) {
                $roleName = $supervisor->roles()->first()?->name ?? 'Supervisor';
                $roleLabel = ucwords(str_replace('_', ' ', $roleName));
                return [
                    'user_id' => $supervisor->id,
                    'name' => trim($supervisor->first_name . ' ' . $supervisor->last_name),
                    'email' => $supervisor->email,
                    'role' => $roleLabel,
                    'designation' => $roleLabel . ($supervisor->department ? ', ' . $supervisor->department->title : ''),
                    'department_id' => $supervisor->department_id,
                    'department_title' => $supervisor->department?->title ?? '',
                    'is_acting' => false,
                    'action_type' => 'submit',
                    'reason' => 'Unit Hierarchy: Submit completed task upward to ' . $roleLabel
                ];
            }
        }

        return self::getSuggestedDirectorForTask($task);
    }

    /**
     * Resolve the suggested Director or acting Deputy Director for a given task.
     *
     * @param Task $task
     * @return array|null
     */
    public static function getSuggestedDirectorForTask(Task $task): ?array
    {
        $targetDeptId = null;
        $reason = '';

        // 1. Check if linked registry File has a department
        if ($task->file && $task->file->department_id) {
            $targetDeptId = $task->file->department_id;
            $dept = Department::find($targetDeptId);
            $reason = 'Registry File Department: ' . ($dept ? $dept->title : 'ID ' . $targetDeptId);
        }

        // 2. Check if task has direct department_id or assigned_by_department
        if (!$targetDeptId && !empty($task->department_id)) {
            $targetDeptId = $task->department_id;
            $dept = Department::find($targetDeptId);
            $reason = 'Task Department: ' . ($dept ? $dept->title : 'ID ' . $targetDeptId);
        }

        // 3. Scan title and description for department keywords if still undetermined
        if (!$targetDeptId) {
            $text = strtolower(($task->title ?? '') . ' ' . ($task->description ?? ''));
            $departments = Department::all();
            foreach ($departments as $dept) {
                $cleanTitle = strtolower(trim($dept->title));
                // Match keywords (avoiding very short words)
                if (strlen($cleanTitle) >= 4 && str_contains($text, $cleanTitle)) {
                    $targetDeptId = $dept->id;
                    $reason = 'Mentioned in task: ' . $dept->title;
                    break;
                }
            }
        }

        // 4. Default for AGF executive tasks without explicit dept -> Administration (Hamza Maigari)
        if (!$targetDeptId) {
            $adminDept = Department::where('title', 'like', '%administration%')->first();
            if ($adminDept) {
                $targetDeptId = $adminDept->id;
                $reason = 'Executive Default: Department of Administration';
            }
        }

        if (!$targetDeptId) {
            return null;
        }

        $targetDept = Department::find($targetDeptId);
        if (!$targetDept) {
            return null;
        }

        // 4b. If originating from AGF Office (super user / registry), route out to operational Director of Administration
        if (str_contains(strtolower($targetDept->title), 'agf')) {
            $adminDept = Department::where('title', 'like', '%administration%')->first();
            if ($adminDept) {
                $targetDeptId = $adminDept->id;
                $targetDept = $adminDept;
                $reason = 'Executive Dispatch from AGF Office -> Routed to Director Administration';
            }
        }

        // 5. Look for active substantive Director
        $director = User::role('director')
            ->where('department_id', $targetDeptId)
            ->first();

        if ($director) {
            return [
                'user_id' => $director->id,
                'name' => trim($director->first_name . ' ' . $director->last_name),
                'email' => $director->email,
                'role' => 'Director',
                'designation' => 'Director, ' . $targetDept->title,
                'department_id' => $targetDept->id,
                'department_title' => $targetDept->title,
                'is_acting' => false,
                'reason' => $reason
            ];
        }

        // 6. Fallback: Look for active Deputy Director (Acting Head)
        $deputyDirector = User::role('deputy director')
            ->where('department_id', $targetDeptId)
            ->first();

        if ($deputyDirector) {
            return [
                'user_id' => $deputyDirector->id,
                'name' => trim($deputyDirector->first_name . ' ' . $deputyDirector->last_name),
                'email' => $deputyDirector->email,
                'role' => 'Deputy Director',
                'designation' => 'Deputy Director (Acting Head), ' . $targetDept->title,
                'department_id' => $targetDept->id,
                'department_title' => $targetDept->title,
                'is_acting' => true,
                'reason' => $reason . ' (Substantive Director unavailable; routed to Deputy Director)'
            ];
        }

        return null;
    }

    /**
     * Resolve the upward supervisor in the routing hierarchy.
     * Hierarchy Chain: Unit Staff -> Assistant Director -> Deputy Director -> Director -> AGF (Super User)
     *
     * @param User $user
     * @param Task|null $task
     * @return User|null
     */
    public static function getUpwardSupervisor(User $user, ?Task $task = null): ?User
    {
        // 1. If task is provided, check strict chain resolver first
        if ($task) {
            $chainApprover = ChainResolver::resolveNextApprover($user, $task);
            if ($chainApprover) {
                return $chainApprover;
            }
        }

        // 2. If task has a parent assignment from a supervisor higher up in rank, check them
        if ($task) {
            $userRank = ChainResolver::getUserRank($user);
            $parentAssign = \App\Models\TaskAssignment::where('task_id', $task->id)
                ->where('assigned_to', $user->id)
                ->where('assigned_by', '!=', $user->id)
                ->latest('id')
                ->get()
                ->first(function ($assign) use ($userRank) {
                    $u = User::find($assign->assigned_by);
                    return $u && ChainResolver::getUserRank($u) > $userRank && !$u->is_on_leave;
                });

            if ($parentAssign && $parentAssign->assigned_by) {
                $supervisor = User::find($parentAssign->assigned_by);
                if ($supervisor && $supervisor->id !== $user->id) {
                    return $supervisor;
                }
            }
        }

        // 2. Upward from Unit Staff / Regular User -> Assistant Director of their unit
        $isRegularUser = $user->hasRole('regular user') || $user->hasRole('regular users') || (!empty($user->unit_id) && !$user->hasAnyRole(['assistant director', 'deputy director', 'director', 'super user']));
        if ($isRegularUser) {
            if ($user->unit_id) {
                $ad = User::role('assistant director')->where('unit_id', $user->unit_id)->first();
                if ($ad) return $ad;
            }
            if ($user->division_id) {
                $dd = User::role('deputy director')->where('division_id', $user->division_id)->first();
                if ($dd) return $dd;
            }
            if ($user->department_id) {
                $dir = User::role('director')->where('department_id', $user->department_id)->first();
                if ($dir) return $dir;
            }
        }

        // 3. Upward from Assistant Director (Unit Head) -> Deputy Director of their division
        if ($user->hasRole('assistant director')) {
            if ($user->division_id) {
                $dd = User::role('deputy director')->where('division_id', $user->division_id)->first();
                if ($dd) return $dd;
            }
            if ($user->department_id) {
                $dir = User::role('director')->where('department_id', $user->department_id)->first();
                if ($dir) return $dir;
            }
        }

        // 4. Upward from Deputy Director (Division Head) -> Director of their department
        if ($user->hasRole('deputy director')) {
            if ($user->department_id) {
                $dir = User::role('director')->where('department_id', $user->department_id)->first();
                if ($dir) return $dir;
            }
        }

        // 5. Upward from Director (Department Head) -> AGF (Super User)
        if ($user->hasRole('director')) {
            $agf = User::role('super user')->first();
            if ($agf) return $agf;
        }

        // 6. Super user (AGF) is the final executive level
        if ($user->hasRole('super user')) {
            return null; // AGF is top of chain
        }

        // Default fallback to AGF if no supervisor matched
        return User::role('super user')->first();
    }
}

