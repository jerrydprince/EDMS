<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;

class ChainResolver
{
    /**
     * Resolves the next approver in the strict chain:
     * Staff -> Assistant Director -> Deputy Director -> Director -> AGF.
     * Automatically skips users who have is_on_leave = true.
     *
     * @param User $user
     * @param Task $task
     * @return User|null
     */
    public static function resolveNextApprover(User $user, Task $task): ?User
    {
        // 1. Regular User / Staff -> Look for Assistant Director (Unit Head)
        if (self::isStaff($user)) {
            $ad = self::findActiveAssistantDirector($user);
            if ($ad) {
                return $ad;
            }
            // If AD on leave or not found, auto-skip to Deputy Director
            $dd = self::findActiveDeputyDirector($user);
            if ($dd) {
                return $dd;
            }
            // If DD on leave or not found, auto-skip to Director
            $dir = self::findActiveDirector($user);
            if ($dir) {
                return $dir;
            }
            // Finally auto-skip to AGF
            return self::findActiveAgf();
        }

        // 2. Assistant Director -> Look for Deputy Director (Division Head)
        if ($user->hasRole('assistant director')) {
            $dd = self::findActiveDeputyDirector($user);
            if ($dd) {
                return $dd;
            }
            // If DD on leave, auto-skip to Director
            $dir = self::findActiveDirector($user);
            if ($dir) {
                return $dir;
            }
            // Finally auto-skip to AGF
            return self::findActiveAgf();
        }

        // 3. Deputy Director -> Look for Director (Department Head)
        if ($user->hasRole('deputy director')) {
            $dir = self::findActiveDirector($user);
            if ($dir) {
                return $dir;
            }
            // If Director on leave, auto-skip to AGF
            return self::findActiveAgf();
        }

        // 4. Director -> Look for AGF (Super User)
        if ($user->hasRole('director')) {
            return self::findActiveAgf();
        }

        // 5. AGF (Super User) -> End of chain (Final Approver)
        if ($user->hasRole('super user') || $user->hasRole('super admin') || $user->hasRole('Accountant-General')) {
            return null;
        }

        // Default fallback to AGF
        return self::findActiveAgf();
    }

    /**
     * Returns eligible skip targets for AD and DD.
     * AD can skip DD -> Director.
     * DD can skip Director -> AGF.
     *
     * @param User $user
     * @param Task $task
     * @return array
     */
    public static function getEligibleSkipTargets(User $user, Task $task): array
    {
        $targets = [];

        if ($user->hasRole('assistant director')) {
            $dir = self::findActiveDirector($user);
            if ($dir && $dir->id !== $user->id) {
                $targets[] = [
                    'id' => $dir->id,
                    'name' => $dir->first_name . ' ' . $dir->last_name,
                    'role' => 'Director',
                    'department' => $dir->department ? $dir->department->title : 'Department Head',
                ];
            }
        } elseif ($user->hasRole('deputy director')) {
            $agf = self::findActiveAgf();
            if ($agf && $agf->id !== $user->id) {
                $targets[] = [
                    'id' => $agf->id,
                    'name' => $agf->first_name . ' ' . $agf->last_name,
                    'role' => 'Accountant-General / Super User',
                    'department' => 'Executive Office',
                ];
            }
        }

        return $targets;
    }

    /**
     * Get hierarchy rank of a user:
     * 5: AGF / Super User / Super Admin / Admin
     * 4: Director
     * 3: Deputy Director
     * 2: Assistant Director
     * 1: Staff / Regular User
     *
     * @param User $user
     * @return int
     */
    public static function getUserRank(User $user): int
    {
        if (self::isAgf($user)) {
            return 5;
        }
        if ($user->hasRole('director')) {
            return 4;
        }
        if ($user->hasRole('deputy director')) {
            return 3;
        }
        if ($user->hasRole('assistant director')) {
            return 2;
        }
        return 1;
    }

    public static function isAgf(User $user): bool
    {
        return $user->hasRole('super user') ||
               $user->hasRole('super admin') ||
               $user->hasRole('Accountant-General') ||
               $user->hasRole('admin');
    }

    /**
     * Resolves immediate supervisor for SLA escalation.
     */
    public static function resolveSupervisor(User $user): ?User
    {
        return self::resolveNextApprover($user, new Task());
    }

    protected static function isStaff(User $user): bool
    {
        return $user->hasRole('regular user') ||
               $user->hasRole('regular users') ||
               (!$user->hasAnyRole(['assistant director', 'deputy director', 'director', 'super user', 'super admin']));
    }

    protected static function findActiveAssistantDirector(User $user): ?User
    {
        $query = User::role('assistant director')->where('is_on_leave', false);
        if ($user->unit_id) {
            $ad = (clone $query)->where('unit_id', $user->unit_id)->first();
            if ($ad) return $ad;
        }
        if ($user->division_id) {
            $ad = (clone $query)->where('division_id', $user->division_id)->first();
            if ($ad) return $ad;
        }
        return (clone $query)->first();
    }

    protected static function findActiveDeputyDirector(User $user): ?User
    {
        $query = User::role('deputy director')->where('is_on_leave', false);
        if ($user->division_id) {
            $dd = (clone $query)->where('division_id', $user->division_id)->first();
            if ($dd) return $dd;
        }
        if ($user->department_id) {
            $dd = (clone $query)->where('department_id', $user->department_id)->first();
            if ($dd) return $dd;
        }
        return (clone $query)->first();
    }

    protected static function findActiveDirector(User $user): ?User
    {
        $query = User::role('director')->where('is_on_leave', false);
        if ($user->department_id) {
            $dir = (clone $query)->where('department_id', $user->department_id)->first();
            if ($dir) return $dir;
        }
        return (clone $query)->first();
    }

    protected static function findActiveAgf(): ?User
    {
        $agf = User::role('super user')->where('is_on_leave', false)->first();
        if (!$agf) {
            $agf = User::role('super admin')->where('is_on_leave', false)->first();
        }
        if (!$agf) {
            $agf = User::role('super user')->first();
        }
        return $agf;
    }
}
