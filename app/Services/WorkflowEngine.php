<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\WorkflowRoute;
use App\Models\WorkflowLog;
use App\Models\TaskAssignment;
use Exception;
use Illuminate\Support\Facades\DB;

class WorkflowEngine
{
    /**
     * Define the strict order of hierarchy.
     */
    protected const HIERARCHY = [
        'accountant_general' => 1,
        'special_adviser' => 2,
        'chief_of_staff' => 3,
        'director' => 4,
        'deputy_director' => 5,
        'assistant_director' => 6,
        'chief_officer' => 7,
        'assistant_chief' => 8,
        'principal_officer_i' => 9,
        'principal_officer_ii' => 10,
        'senior_officer' => 11,
        'officer' => 12
    ];

    /**
     * Get the user's highest workflow role/level based on their attributes.
     */
    public function getUserLevel(User $user)
    {
        if ($user->hasRole('Accountant-General') || $user->hasRole('super user')) return 'accountant_general';
        if ($user->hasRole('Special Adviser / Technical Adviser')) return 'special_adviser';
        if ($user->hasRole('Chief of Staff')) return 'chief_of_staff';

        // Map by Grade Level
        $gl = (int) $user->grade_level;
        if ($gl >= 17) return 'director';
        if ($gl === 16) return 'deputy_director';
        if ($gl === 15) return 'assistant_director';
        if ($gl === 14) return 'chief_officer';
        if ($gl === 13) return 'assistant_chief';
        if ($gl === 12) return 'principal_officer_i';
        if ($gl === 10) return 'principal_officer_ii';
        if ($gl === 9) return 'senior_officer';
        if ($gl <= 8) return 'officer';

        return 'officer'; // fallback
    }

    /**
     * Get valid target levels for downward delegation, handling optional skips.
     */
    protected function getValidNextDownLevels($currentLevel)
    {
        $hierarchyValue = self::HIERARCHY[$currentLevel];
        $validLevels = [];
        
        foreach (self::HIERARCHY as $level => $val) {
            // Strictly the next immediate level
            if ($val === $hierarchyValue + 1) {
                $validLevels[] = $level;
            }
        }
        
        // Handle optional top executive tier skipping
        if ($currentLevel === 'accountant_general') {
            $validLevels = ['special_adviser', 'chief_of_staff', 'director'];
        }
        if ($currentLevel === 'special_adviser' || $currentLevel === 'chief_of_staff') {
            $validLevels = ['director'];
        }
        
        return $validLevels;
    }

    /**
     * Get the string name of the next level up.
     */
    protected function getNextLevelUp($currentLevel)
    {
        $levels = array_keys(self::HIERARCHY);
        $currentIndex = array_search($currentLevel, $levels);
        
        if ($currentIndex !== false && isset($levels[$currentIndex - 1])) {
            return $levels[$currentIndex - 1];
        }

        return null;
    }

    /**
     * Initialize a new workflow route when a task is created by the head.
     */
    public function initializeWorkflow(Task $task, User $creator)
    {
        $level = $this->getUserLevel($creator);
        
        return WorkflowRoute::create([
            'task_id' => $task->id,
            'current_level' => $level,
            'direction' => 'downward_delegation',
            'status' => 'pending'
        ]);
    }

    /**
     * Delegate task downwards in the hierarchy.
     */
    public function delegate(Task $task, User $fromUser, array $toUserIds, $comments = '')
    {
        return DB::transaction(function () use ($task, $fromUser, $toUserIds, $comments) {
            $route = WorkflowRoute::where('task_id', $task->id)->first();
            if (!$route) {
                $route = $this->initializeWorkflow($task, $fromUser);
            }

            if ($route->direction === 'upward_approval') {
                throw new Exception("Workflow is currently in approval phase and cannot be delegated downwards unless returned.");
            }

            $currentLevel = $this->getUserLevel($fromUser);
            $validNextLevels = $this->getValidNextDownLevels($currentLevel);

            if (empty($validNextLevels)) {
                throw new Exception("Cannot delegate further down. Already at lowest level.");
            }

            $routeNextLevel = null;

            // Assign tasks to the users
            foreach ($toUserIds as $toUserId) {
                $toUser = User::find($toUserId);
                if (!$toUser) continue;

                $targetLevel = $this->getUserLevel($toUser);
                
                // Validate hierarchy: User must delegate to a valid subordinate level
                if (!in_array($targetLevel, $validNextLevels)) {
                    $allowedStr = implode(', ', $validNextLevels);
                    throw new Exception("Strict routing violation: Allowed targets for your level are ({$allowedStr}). Target user is level: {$targetLevel}");
                }

                // Server-Side Organizational Access Control Enforcement
                if ($currentLevel !== 'accountant_general' && $currentLevel !== 'special_adviser' && $currentLevel !== 'chief_of_staff') {
                    if ($fromUser->department_id && $fromUser->department_id !== $toUser->department_id) {
                        throw new Exception("Security Violation: You can only delegate tasks to staff within your own Department.");
                    }
                    if ($fromUser->division_id && $fromUser->division_id !== $toUser->division_id) {
                        throw new Exception("Security Violation: You can only delegate tasks to staff within your own Division.");
                    }
                    if ($fromUser->unit_id && $fromUser->unit_id !== $toUser->unit_id) {
                        throw new Exception("Security Violation: You can only delegate tasks to staff within your own Unit.");
                    }
                }

                $routeNextLevel = $targetLevel;

                TaskAssignment::updateOrCreate(
                    ['task_id' => $task->id, 'assigned_to' => $toUserId],
                    ['assigned_by' => $fromUser->id, 'status' => 'assigned', 'description' => $comments]
                );

                WorkflowLog::create([
                    'task_id' => $task->id,
                    'action_by' => $fromUser->id,
                    'action_to' => $toUserId,
                    'action_type' => 'delegated',
                    'comments' => $comments
                ]);
            }

            // Update route state
            $route->update([
                'current_level' => $routeNextLevel ?: $validNextLevels[0],
                'status' => 'in_progress'
            ]);

            return $route;
        });
    }

    /**
     * Staff submits the work upwards to their supervisor.
     */
    public function submit(Task $task, User $fromUser, $comments = '')
    {
        return DB::transaction(function () use ($task, $fromUser, $comments) {
            $route = WorkflowRoute::where('task_id', $task->id)->firstOrFail();
            $currentLevel = $this->getUserLevel($fromUser);
            
            if ($currentLevel === 'accountant_general') {
                throw new Exception("The Accountant-General cannot submit upwards.");
            }

            $assignment = TaskAssignment::where('task_id', $task->id)
                ->where('assigned_to', $fromUser->id)
                ->firstOrFail();
                
            $assignment->update(['status' => 'completed']);

            // Find the supervisor (who assigned it to this staff)
            $supervisorId = $assignment->assigned_by;
            
            WorkflowLog::create([
                'task_id' => $task->id,
                'action_by' => $fromUser->id,
                'action_to' => $supervisorId,
                'action_type' => 'submitted',
                'comments' => $comments
            ]);

            // Dynamically set the route level to the supervisor's level to support skipped tiers
            $supervisor = User::find($supervisorId);
            $supervisorLevel = $supervisor ? $this->getUserLevel($supervisor) : $this->getNextLevelUp($currentLevel);

            // Change workflow to upward approval
            $route->update([
                'current_level' => $supervisorLevel,
                'direction' => 'upward_approval',
                'status' => 'pending'
            ]);

            return $route;
        });
    }

    /**
     * Supervisor approves the task and routes it upwards.
     */
    public function approve(Task $task, User $fromUser, $comments = '')
    {
        return DB::transaction(function () use ($task, $fromUser, $comments) {
            $route = WorkflowRoute::where('task_id', $task->id)->firstOrFail();
            $currentLevel = $this->getUserLevel($fromUser);
            
            if ($route->direction !== 'upward_approval') {
                throw new Exception("Task is not in the approval phase.");
            }

            // Find the person who assigned the task to this supervisor during delegation
            // so we route it back to them.
            $delegationLog = WorkflowLog::where('task_id', $task->id)
                ->where('action_type', 'delegated')
                ->where('action_to', $fromUser->id)
                ->latest()
                ->first();

            if ($delegationLog && $delegationLog->action_by) {
                $supervisor = User::find($delegationLog->action_by);
                $nextLevel = $supervisor ? $this->getUserLevel($supervisor) : $this->getNextLevelUp($currentLevel);
                $supervisorId = $delegationLog->action_by;
            } else {
                $nextLevel = null;
                $supervisorId = null;
            }

            if (!$nextLevel) {
                // Already at the top of the chain - Final Approval
                $route->update(['status' => 'approved', 'current_level' => 'accountant_general']);
                $task->update(['status' => 'completed', 'progress' => 100]);
                
                WorkflowLog::create([
                    'task_id' => $task->id,
                    'action_by' => $fromUser->id,
                    'action_to' => null,
                    'action_type' => 'approved',
                    'comments' => 'Final Approval: ' . $comments
                ]);

                return $route;
            }

            WorkflowLog::create([
                'task_id' => $task->id,
                'action_by' => $fromUser->id,
                'action_to' => $supervisorId,
                'action_type' => 'approved',
                'comments' => $comments
            ]);

            $route->update([
                'current_level' => $nextLevel,
                'status' => 'pending'
            ]);

            return $route;
        });
    }

    /**
     * Supervisor rejects the task and sends it one step down.
     */
    public function returnTask(Task $task, User $fromUser, $comments = '')
    {
        return DB::transaction(function () use ($task, $fromUser, $comments) {
            $route = WorkflowRoute::where('task_id', $task->id)->firstOrFail();
            $currentLevel = $this->getUserLevel($fromUser);

            // Find who submitted/approved it to this user
            $lastUpwardLog = WorkflowLog::where('task_id', $task->id)
                ->whereIn('action_type', ['submitted', 'approved'])
                ->where('action_to', $fromUser->id)
                ->latest()
                ->first();

            if (!$lastUpwardLog) {
                throw new Exception("Cannot return task. No upward submission found.");
            }

            $returnToUser = User::find($lastUpwardLog->action_by);
            $nextLevel = $returnToUser ? $this->getUserLevel($returnToUser) : null;
            $returnToId = $returnToUser ? $returnToUser->id : null;

            WorkflowLog::create([
                'task_id' => $task->id,
                'action_by' => $fromUser->id,
                'action_to' => $returnToId,
                'action_type' => 'returned',
                'comments' => $comments
            ]);

            // Update route to fall back one step
            $route->update([
                'current_level' => $nextLevel,
                'direction' => 'upward_approval', // Still in approval phase, but sent back for corrections
                'status' => 'returned'
            ]);

            return $route;
        });
    }
}
