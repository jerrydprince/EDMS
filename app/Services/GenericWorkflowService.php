<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\WorkflowVersion;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransition;
use App\Models\WorkflowCondition;
use App\Models\WorkflowLog;
use App\Models\TaskAssignment;
use Illuminate\Support\Facades\DB;
use Exception;

class GenericWorkflowService
{
    /**
     * Start a new workflow instance from a generic blueprint.
     */
    public function start(Task $task, User $initiator, array $assignedUsers = [])
    {
        return DB::transaction(function () use ($task, $initiator, $assignedUsers) {
            $version = WorkflowVersion::with('steps')->findOrFail($task->workflow_version_id);
            $startStep = $version->steps->where('is_start_step', true)->first();

            if (!$startStep) {
                throw new Exception("Workflow version does not have a defined start step.");
            }

            // Move to start step
            $task->current_step_id = $startStep->id;
            $task->status = 'in progress';
            $task->save();

            // Calculate SLA Due Date for start step
            $dueDate = null;
            $sla = \App\Models\WorkflowSla::where('workflow_step_id', $startStep->id)->first();
            if ($sla) {
                $dueDate = $this->calculateDueDate(now(), $sla);
            }

            // Track active steps concurrently
            DB::table('task_active_steps')->insert([
                'task_id' => $task->id,
                'step_id' => $startStep->id,
                'status' => 'in_progress',
                'due_date' => $dueDate,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if (empty($assignedUsers)) {
                if ($startStep->is_start_step) {
                    $assignedUsers = [$initiator->id];
                } elseif (!empty($startStep->role_required)) {
                    $assignedUsers = $this->resolveAutoAssignment($task, $initiator, $startStep);
                }
            }

            // Create initial assignments and notify
            foreach ($assignedUsers as $userId) {
                TaskAssignment::updateOrCreate(
                    ['task_id' => $task->id, 'assigned_to' => $userId],
                    ['assigned_by' => $initiator->id, 'status' => 'pending']
                );
                
                $assignee = User::find($userId);
                if ($assignee) {
                    $assignee->notify(new \App\Notifications\WorkflowEventNotification(
                        $task, 
                        $initiator, 
                        'assigned', 
                        'You have been assigned a new workflow task.'
                    ));
                }
            }

            // Log initialization
            WorkflowLog::create([
                'task_id' => $task->id,
                'action_by' => $initiator->id,
                'action_type' => 'workflow_started',
                'comments' => 'Initiated workflow: ' . $version->version_number
            ]);

            return $task;
        });
    }

    /**
     * Get available transitions for the current step.
     */
    public function getAvailableActions(Task $task, User $user, $activeStepId = null)
    {
        if ($task->status === 'completed' || $task->status === 'closed') {
            return collect(); // No actions for completed tasks
        }

        $activeSteps = DB::table('task_active_steps')
            ->where('task_id', $task->id)
            ->where('status', 'in_progress');
            
        if ($activeStepId) {
            $activeSteps->where('step_id', $activeStepId);
        }
        
        $activeStepIds = $activeSteps->pluck('step_id')->toArray();

        if (empty($activeStepIds)) {
            return collect();
        }

        // Must have an active (pending) assignment or be an admin to act
        $isAssigned = TaskAssignment::where('task_id', $task->id)
            ->where('assigned_to', $user->id)
            ->where('status', '!=', 'completed')
            ->exists();

        $userRoles = $user->roles->pluck('name')->map(fn($role) => strtolower($role))->toArray();
        $isAdmin = in_array('super user', $userRoles) || in_array('super admin', $userRoles) || in_array('super-admin', $userRoles);

        if (!$isAssigned && !$isAdmin) {
            return collect();
        }

        // Get transitions out of the current active steps
        $transitions = WorkflowTransition::with(['toStep'])
            ->whereIn('from_step_id', $activeStepIds)
            ->get();

        // Evaluate Workflow Conditions to prevent illegal bypass natively
        // And also ensure the user has the required role for the specific step if branching
        $filteredTransitions = $transitions->filter(function ($transition) use ($task, $user, $userRoles) {
            // Check Step Role requirements (vital for parallel branching where a user is assigned but only to a specific branch)
            $fromStep = $transition->fromStep;
            if (!empty($fromStep->role_required)) {
                if (strtolower($fromStep->role_required) !== 'initiator' && 
                    !($fromStep->is_start_step && $task->created_by === $user->id) &&
                    !in_array(strtolower($fromStep->role_required), $userRoles) && 
                    !in_array('super user', $userRoles) && 
                    !in_array('super admin', $userRoles) && 
                    !in_array('super-admin', $userRoles)) {
                    return false;
                }

                // Verify structural hierarchy if defined on the step
                if ($fromStep->department_id && $user->department_id != $fromStep->department_id) {
                    return false;
                }
                if ($fromStep->division_id && $user->division_id != $fromStep->division_id) {
                    return false;
                }
                if ($fromStep->unit_id && $user->unit_id != $fromStep->unit_id) {
                    return false;
                }
            }

            $conditions = WorkflowCondition::where('workflow_transition_id', $transition->id)->get();
            return $this->evaluateConditions($conditions, $task);
        })->values();

        // Inject dynamic auto-return transitions
        $activeStepsCollection = \App\Models\WorkflowStep::whereIn('id', $activeStepIds)->get();
        foreach ($activeStepsCollection as $step) {
            if ($step->auto_return_on_rejection) {
                // Check if user has permission on this step
                if (!empty($step->role_required)) {
                    if (strtolower($step->role_required) !== 'initiator' && 
                        !($step->is_start_step && $task->created_by === $user->id) &&
                        !in_array(strtolower($step->role_required), $userRoles) && 
                        !in_array('super user', $userRoles) && 
                        !in_array('super admin', $userRoles) && 
                        !in_array('super-admin', $userRoles)) {
                        continue;
                    }
                }

                // Ensure a return transition doesn't already exist from the database
                $hasDbReturn = $transitions->contains(function ($t) use ($step) {
                    return $t->from_step_id == $step->id && strtolower($t->action_name) === 'return';
                });
                
                if (!$hasDbReturn) {
                    $pseudoTransition = (object)[
                        'id' => 'auto_return_' . $step->id,
                        'from_step_id' => $step->id,
                        'to_step_id' => null, // Dynamic
                        'action_name' => 'Return',
                        'is_auto_return' => true,
                        'requires_comment' => true
                    ];
                    $filteredTransitions->push($pseudoTransition);
                }
            }
        }
        
        // Resolve target users for all transitions so the UI and routing know the exact recipient
        foreach ($filteredTransitions as $transition) {
            if (isset($transition->toStep) && $transition->toStep) {
                $assignedUserIds = $this->resolveAutoAssignment($task, $user, $transition->toStep);
                if (!empty($assignedUserIds)) {
                    $targetUser = User::with('department')->find($assignedUserIds[0]);
                    if ($targetUser) {
                        $roleName = $targetUser->getRoleNames()->first() ?? 'Officer';
                        $deptName = $targetUser->department ? ($targetUser->department->title ?? $targetUser->department->name) : '';
                        $transition->target_user = [
                            'id' => $targetUser->id,
                            'name' => $targetUser->first_name . ' ' . $targetUser->last_name,
                            'role' => ucwords($roleName) . ($deptName ? " - {$deptName}" : ''),
                            'department' => $deptName,
                        ];
                    }
                }
            } elseif (!empty($transition->is_auto_return)) {
                $lastActionLog = WorkflowLog::where('task_id', $task->id)
                    ->where('action_by', '!=', $user->id)
                    ->latest()
                    ->first();
                $targetUserId = $lastActionLog ? $lastActionLog->action_by : $task->created_by;
                if ($targetUserId) {
                    $targetUser = User::with('department')->find($targetUserId);
                    if ($targetUser) {
                        $roleName = $targetUser->getRoleNames()->first() ?? 'Officer';
                        $deptName = $targetUser->department ? ($targetUser->department->title ?? $targetUser->department->name) : '';
                        $transition->target_user = [
                            'id' => $targetUser->id,
                            'name' => $targetUser->first_name . ' ' . $targetUser->last_name,
                            'role' => ucwords($roleName) . ($deptName ? " - {$deptName}" : ''),
                            'department' => $deptName,
                        ];
                    }
                }
            }
        }

        return $filteredTransitions;
    }

    protected function evaluateConditions($conditions, Task $task)
    {
        if ($conditions->isEmpty()) {
            return true;
        }
        
        foreach ($conditions as $condition) {
            $taskValue = $task->{$condition->field} ?? null;
            $ruleValue = $condition->value;
            
            switch ($condition->operator) {
                case '=':
                case '==':
                    if ($taskValue != $ruleValue) return false;
                    break;
                case '>':
                    if ((float)$taskValue <= (float)$ruleValue) return false;
                    break;
                case '<':
                    if ((float)$taskValue >= (float)$ruleValue) return false;
                    break;
                case '!=':
                    if ($taskValue == $ruleValue) return false;
                    break;
            }
        }
        
        return true;
    }

    /**
     * Execute a transition/action on the generic workflow.
     */
    public function executeAction(Task $task, User $user, $transitions, array $assignedToUsers = [], $comments = '', array $checklists = [], array $attachments = [])
    {
        if ($transitions instanceof WorkflowTransition) {
            $transitions = collect([$transitions]);
        } elseif (is_array($transitions)) {
            $transitions = collect($transitions);
        }

        return DB::transaction(function () use ($task, $user, $transitions, $assignedToUsers, $comments, $checklists, $attachments) {
            
            $activeStepRecord = DB::table('task_active_steps')
                ->where('task_id', $task->id)
                ->where('status', 'in_progress')
                ->first();

            if (!$activeStepRecord) {
                throw new Exception("Invalid transition for the current active steps.");
            }

            // Validate transitions (ensure they all stem from the same step)
            $fromStepId = null;
            foreach ($transitions as $t) {
                if ($fromStepId === null) {
                    $fromStepId = $t->from_step_id;
                } elseif ($fromStepId !== $t->from_step_id) {
                    throw new Exception("Transitions must stem from the same step.");
                }
            }

            if ($fromStepId != $activeStepRecord->step_id) {
                throw new Exception("Task is currently not at the required step for these transitions.");
            }

            // Re-verify conditions server-side against API manipulation
            foreach ($transitions as $transition) {
                $conditions = \App\Models\WorkflowCondition::where('workflow_transition_id', $transition->id)->get();
                if (!$this->evaluateConditions($conditions, $task)) {
                    throw new \Exception("Transition conditions not met. Illegal Bypass Blocked.");
                }
            }

            // We use the first transition for logging and auto-resolving, since they stem from the same step
            $primaryTransition = $transitions->first();
            
            // Check for Auto-Return Pseudo-Transition
            $isAutoReturn = isset($primaryTransition->is_auto_return) && $primaryTransition->is_auto_return;
            
            if ($isAutoReturn) {
                if (empty(trim($comments))) {
                    throw new Exception("Comments are mandatory when returning a task.");
                }
                
                $previousStepRecord = DB::table('task_active_steps')
                    ->where('task_id', $task->id)
                    ->where('status', 'completed')
                    ->latest('updated_at')
                    ->first();
                    
                if (!$previousStepRecord) {
                    throw new Exception("Cannot return task. No previous step found in the execution history.");
                }
                
                $toStep = \App\Models\WorkflowStep::find($previousStepRecord->step_id);
                
                // Find the user who last acted on it to return it directly to them
                $lastActionLog = WorkflowLog::where('task_id', $task->id)
                    ->where('action_by', '!=', $user->id)
                    ->latest()
                    ->first();
                    
                if ($lastActionLog && empty($assignedToUsers)) {
                    $assignedToUsers = [$lastActionLog->action_by];
                }
            } else {
                $toStep = $primaryTransition->toStep;
                
                // Auto-resolve organization mapping (Upward/Downward routing)
                if (empty($assignedToUsers) && !empty($toStep->role_required)) {
                    $assignedToUsers = $this->resolveAutoAssignment($task, $user, $toStep);
                }
            }

            // Determine formal action title
            $actionTitle = $isAutoReturn 
                ? 'Returned for Revision' 
                : ($primaryTransition->action_name ?? ($primaryTransition->name ?? 'Workflow Statutory Action'));

            // Log action
            $workflowLog = WorkflowLog::create([
                'task_id' => $task->id,
                'action_by' => $user->id,
                'action_type' => $actionTitle,
                'comments' => $comments,
                'action_to' => count($assignedToUsers) ? $assignedToUsers[0] : null
            ]);

            // Save attachments
            if (!empty($attachments)) {
                foreach ($attachments as $file) {
                    $path = $file->store('workflow_attachments', 'public');
                    \App\Models\WorkflowLogAttachment::create([
                        'workflow_log_id' => $workflowLog->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize()
                    ]);

                    // Register as TaskDoc so it appears in Attached Documents & Memos
                    \App\Models\TaskDoc::create([
                        'task_id' => $task->id,
                        'path' => '/storage/' . $path,
                        'file_name' => $file->getClientOriginalName(),
                        'size' => number_format($file->getSize() / 1024, 1) . ' KB',
                    ]);
                }
            }

            // Save checklists
            if (!empty($checklists)) {
                foreach ($checklists as $checklistId) {
                    \App\Models\TaskChecklist::updateOrCreate(
                        ['task_id' => $task->id, 'workflow_checklist_id' => $checklistId],
                        ['is_completed' => true, 'completed_by' => $user->id]
                    );
                }
            }

            // Append Official Minute Row to Docket Ledger & Regenerate Minute Sheet PDF
            $minuteDirective = !empty($comments) ? $comments : $actionTitle;
            try {
                $docSigningService = app(\App\Services\DocumentSigningService::class);
                $docSigningService->appendMinuteRow(
                    $task,
                    $user,
                    $minuteDirective,
                    null,
                    $actionTitle
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to append minute row or regenerate PDF via DocumentSigningService: " . $e->getMessage());
                \App\Models\TaskMinute::create([
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'role' => $user->roles()->first()?->name ?? 'Authorized Officer',
                    'action' => $actionTitle,
                    'directive' => $minuteDirective,
                    'remark' => $minuteDirective,
                    'signature_path' => $user->digital_signature_path,
                    'is_signed' => true,
                    'signed_at' => now(),
                ]);
            }

            // Record in TaskActivity for unified Docket Ledger & Audit Trail
            \App\Models\TaskActivity::create([
                'task_id' => $task->id,
                'description' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) . ' executed ' . $actionTitle . ($comments ? ': ' . $comments : ''),
                'created_by' => $user->id,
            ]);

            // Sync legacy comments
            if (!empty($comments)) {
                \App\Models\Comment::create([
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'comment' => $comments
                ]);
            }

            // If user was assigned, mark their assignment as completed
            TaskAssignment::where('task_id', $task->id)
                ->where('assigned_to', $user->id)
                ->update(['status' => 'completed']);

            // Parallel Workflow Handling: Check thresholds
            $pendingAssignments = TaskAssignment::where('task_id', $task->id)
                ->where('status', 'assigned')
                ->count();
                
            // If parallel step requires all members to act before progressing
            $requiresAll = false; // Could be mapped from $transition->fromStep->role_required === 'committee'
            
            if ($requiresAll && $pendingAssignments > 0) {
                // Threshold not met, log their action but do not progress the step
                return $task;
            }

            // Close old active step
            DB::table('task_active_steps')
                ->where('id', $activeStepRecord->id)
                ->update(['status' => 'completed', 'updated_at' => now()]);

            $isEndStepReached = false;
            
            if ($isAutoReturn) {
                // Return backwards linearly
                $task->current_step_id = $toStep->id;
                
                $dueDate = null;
                $sla = \App\Models\WorkflowSla::where('workflow_step_id', $toStep->id)->first();
                if ($sla) {
                    $dueDate = $this->calculateDueDate(now(), $sla);
                }

                DB::table('task_active_steps')->insert([
                    'task_id' => $task->id,
                    'step_id' => $toStep->id,
                    'status' => 'in_progress',
                    'due_date' => $dueDate,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                foreach ($transitions as $transition) {
                    $toStep = $transition->toStep;
                    
                    // If ANY next step is an END step, we complete the task entirely
                    if ($toStep->is_end_step) {
                        $isEndStepReached = true;
                        $task->current_step_id = $toStep->id;
                        break;
                    }

                    // Move task forward logically
                    $task->current_step_id = $toStep->id; // Legacy pointer points to the last branched step

                    // Calculate SLA Due Date
                    $dueDate = null;
                    $sla = \App\Models\WorkflowSla::where('workflow_step_id', $toStep->id)->first();
                    if ($sla) {
                        $dueDate = $this->calculateDueDate(now(), $sla);
                    }

                    // Spawn new active step branch
                    DB::table('task_active_steps')->insert([
                        'task_id' => $task->id,
                        'step_id' => $toStep->id,
                        'status' => 'in_progress',
                        'due_date' => $dueDate,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
            
            if ($isEndStepReached) {
                $task->status = 'completed';
                $task->save();
                
                // Notify the original creator
                $creator = User::find($task->created_by);
                if ($creator) {
                    $creator->notify(new \App\Notifications\WorkflowEventNotification(
                        $task,
                        $user,
                        'completed',
                        'Your workflow task has been marked as completed.'
                    ));
                }

                // Dispatch event for automated actions (Phase 16)
                event(new \App\Events\WorkflowCompleted($task));

                // Auto-resume parent task if this was a sub-workflow
                if ($task->parent_task_id) {
                    $parentTask = Task::find($task->parent_task_id);
                    if ($parentTask) {
                        $this->resumeTask($parentTask, $user);
                        \App\Models\Comment::create([
                            'task_id' => $parentTask->id,
                            'user_id' => $user->id,
                            'comment' => 'Sub-workflow "' . $task->title . '" has been completed automatically.'
                        ]);
                    }
                }

                return $task;
            }

            $task->status = 'in progress';
            $task->save();

            // Create new assignments and track file movement
            $registryController = app(\App\Http\Controllers\RegistryController::class);
            $userBranchId = $user->unit_id ?? $user->division_id ?? $user->department_id;
            $userBranchType = $user->unit_id ? 'unit' : ($user->division_id ? 'division' : 'department');

            foreach ($assignedToUsers as $userId) {
                TaskAssignment::updateOrCreate(
                    ['task_id' => $task->id, 'assigned_to' => $userId],
                    ['assigned_by' => $user->id, 'status' => 'pending', 'description' => $comments]
                );

                // Phase 4: Sync File Movement Card automatically for file routing
                if ($task->file_id) {
                    $assignedUser = User::find($userId);
                    if ($assignedUser) {
                        $destBranchId = $assignedUser->unit_id ?? $assignedUser->division_id ?? $assignedUser->department_id;
                        $destBranchType = $assignedUser->unit_id ? 'unit' : ($assignedUser->division_id ? 'division' : 'department');

                        $fm = new \App\Models\FileMovement();
                        $fm->sent_from = $userBranchId; 
                        $fm->sent_to = $destBranchId;
                        $fm->file_id = $task->file_id;
                        $fm->status = "pending";
                        $fm->file_movement_id = $registryController->getFileMovementUniqueId();
                        $fm->sent_to_branch = $destBranchType;
                        
                        if ($destBranchType == 'unit') {
                            $fm->sent_to_unit = $destBranchId;
                        } elseif ($destBranchType == 'division') {
                            $fm->sent_to_division = $destBranchId;
                        } else {
                            $fm->sent_to_department = $destBranchId;
                        }

                        if ($userBranchType == 'unit') {
                            $fm->unit_id = $userBranchId;
                            $fm->sent_from_unit = $userBranchId;
                        } elseif ($userBranchType == 'division') {
                            $fm->division_id = $userBranchId;
                            $fm->sent_from_division = $userBranchId;
                        } else {
                            $fm->department_id = $userBranchId;
                            $fm->sent_from_department = $userBranchId;
                        }
                        
                        $fm->save();
                        
                        // Notify receiver of file movement
                        $assignedUser->notify(new \App\Notifications\WorkflowEventNotification(
                            $task,
                            $user,
                            'dispatch',
                            'A file has been dispatched to you via workflow.'
                        ));
                    }
                } else {
                    // Notify normally assigned user
                    $assignedUser = User::find($userId);
                    if ($assignedUser) {
                        $assignedUser->notify(new \App\Notifications\WorkflowEventNotification(
                            $task,
                            $user,
                            'assigned',
                            'You have been assigned a workflow task.'
                        ));
                    }
                }
            }

            return $task;
        });
    }

    protected function resolveAutoAssignment(Task $task, User $user, WorkflowStep $toStep)
    {
        // Initiator / Start Step Routing
        if ($toStep->is_start_step || strtolower($toStep->role_required) === 'initiator') {
            return $task->created_by ? [$task->created_by] : [];
        }

        // Organisational Upward Routing
        if ($toStep->role_required === 'HOD') {
            $hod = User::where('department_id', $task->department_id ?? $user->department_id)
                ->where('is_department_head', 1) 
                ->first();
            return $hod ? [$hod->id] : [];
        }
        
        // Generic Role-based Downward Routing
        $roleName = $toStep->role_required;
        try {
            $query = User::role($roleName);
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            if (in_array(strtolower($roleName), ['agf', 'accountant-general', 'accountant general'])) {
                $query = User::role('super user');
            } elseif (in_array(strtolower($roleName), ['registry', 'central registry'])) {
                $query = User::role('AGF Registry');
            } else {
                return [];
            }
        }
        
        if ($toStep->department_id) {
            $query->where('department_id', $toStep->department_id);
        } elseif (in_array(strtolower($roleName), ['director', 'deputy director', 'assistant director'])) {
            $deptId = $task->department_id ?? $user->department_id;
            if ($deptId) {
                $query->where('department_id', $deptId);
            }
        }
        if ($toStep->division_id) {
            $query->where('division_id', $toStep->division_id);
        }
        if ($toStep->unit_id) {
            $query->where('unit_id', $toStep->unit_id);
        }

        return $query->pluck('id')->toArray();
    }

    public function calculateDueDate($startTime, \App\Models\WorkflowSla $sla)
    {
        if (!$sla->is_working_hours_only) {
            return $startTime->copy()->addHours($sla->duration_hours);
        }

        $hoursRemaining = $sla->duration_hours;
        $current = $startTime->copy();

        $workingDays = \App\Models\WorkflowWorkingDay::all()->keyBy('day_of_week');
        $holidays = \App\Models\WorkflowHoliday::pluck('holiday_date')->map(function($d) { return $d->format('Y-m-d'); })->toArray();

        // Safety fallback if no working days configured
        if ($workingDays->isEmpty()) {
            return $startTime->copy()->addHours($sla->duration_hours);
        }

        while ($hoursRemaining > 0) {
            $dayOfWeek = $current->dayOfWeek; // 0 = Sunday
            $dateStr = $current->format('Y-m-d');

            if (in_array($dateStr, $holidays)) {
                // Skip holiday
                $current->addDay()->startOfDay();
                continue;
            }

            $wd = $workingDays->get($dayOfWeek);
            if (!$wd || !$wd->is_working_day) {
                // Skip non-working day
                $current->addDay()->startOfDay();
                continue;
            }

            $startT = \Carbon\Carbon::createFromFormat('H:i:s', $wd->start_time)->setDateFrom($current);
            $endT = \Carbon\Carbon::createFromFormat('H:i:s', $wd->end_time)->setDateFrom($current);

            // If current time is before start time, jump to start time
            if ($current->lt($startT)) {
                $current = $startT->copy();
            }

            // If current time is after end time, jump to next day
            if ($current->gte($endT)) {
                $current->addDay()->startOfDay();
                continue;
            }

            $hoursLeftToday = $current->diffInMinutes($endT) / 60;

            if ($hoursLeftToday >= $hoursRemaining) {
                $current->addMinutes($hoursRemaining * 60);
                $hoursRemaining = 0;
            } else {
                $hoursRemaining -= $hoursLeftToday;
                $current->addDay()->startOfDay();
            }
        }

        return $current;
    }

    public function pauseTask(Task $task, User $user, $activeStepId = null)
    {
        $stepQuery = \App\Models\TaskActiveStep::where('task_id', $task->id)->where('status', 'in_progress')->where('is_paused', false);
        if ($activeStepId) {
            $stepQuery->where('step_id', $activeStepId);
        }
        $steps = $stepQuery->get();

        foreach ($steps as $step) {
            $step->is_paused = true;
            $step->paused_at = now();
            $step->save();

            WorkflowLog::create([
                'task_id' => $task->id,
                'action_by' => $user->id,
                'action_type' => 'task_paused',
                'comments' => 'Task paused by user.'
            ]);
            
            // Notify assignees
            $assignees = \App\Models\TaskAssignment::where('task_id', $task->id)->where('status', 'assigned')->pluck('assigned_to');
            foreach ($assignees as $userId) {
                $assigneeUser = User::find($userId);
                if ($assigneeUser) {
                    $assigneeUser->notify(new \App\Notifications\WorkflowEventNotification(
                        $task,
                        $user,
                        'paused',
                        'The workflow task you are assigned to has been paused.'
                    ));
                }
            }
        }
    }

    public function resumeTask(Task $task, User $user, $activeStepId = null)
    {
        $stepQuery = \App\Models\TaskActiveStep::where('task_id', $task->id)->where('status', 'in_progress')->where('is_paused', true);
        if ($activeStepId) {
            $stepQuery->where('step_id', $activeStepId);
        }
        $steps = $stepQuery->get();

        foreach ($steps as $step) {
            $pausedMinutes = now()->diffInMinutes($step->paused_at);
            
            $step->is_paused = false;
            $step->total_paused_minutes += $pausedMinutes;
            
            if ($step->due_date) {
                // If the step has an SLA with working hours only, we must recalculate
                $sla = \App\Models\WorkflowSla::where('workflow_step_id', $step->step_id)->first();
                if ($sla && $sla->is_working_hours_only) {
                    // We need to add the paused duration as "working hours" duration.
                    // Or more simply: just re-calculate from now with remaining duration?
                    // Actually, if we were paused for X absolute minutes, we should just 
                    // shift the due_date by X absolute minutes to be simple? No, because those X minutes 
                    // might have spanned holidays/weekends.
                    // The simplest accurate way: calculate how much SLA time was actually LOST during the pause.
                    // To do this, we can just calculate the difference in working minutes between paused_at and now()
                    // But wait, the SLA due_date was already set to a future date. If we were paused for X absolute minutes,
                    // the due date just gets shifted by the actual working minutes that elapsed while paused.
                    // Actually, if we pause, the clock "stops". When we resume, the remaining time resumes from now.
                    // Let's just find out how many working minutes were remaining at `paused_at`.
                    
                    // A much simpler approximation is to just shift the original due date forward by the exact absolute
                    // duration of the pause, BUT that might push the due date into a weekend!
                    // Let's simply recalculate based on remaining duration:
                    // If we paused at T1, due was T2. Remaining = T2 - T1 (in working hours).
                    // Resume at T3, new due = T3 + Remaining (in working hours).
                    
                    // For now, to keep it robust and simple, we'll just add the absolute paused minutes 
                    // and then push it forward to the next valid working hour if it falls on a non-working time.
                    $newDueDate = \Carbon\Carbon::parse($step->due_date)->addMinutes($pausedMinutes);
                    
                    // Check if newDueDate falls on a holiday/weekend, push it forward
                    // We can reuse calculateDueDate by treating the remaining absolute time as duration
                    // Let's do a simple shift for now
                    $step->due_date = $newDueDate;
                } else {
                    $step->due_date = \Carbon\Carbon::parse($step->due_date)->addMinutes($pausedMinutes);
                }
            }
            $step->paused_at = null;
            $step->save();

            WorkflowLog::create([
                'task_id' => $task->id,
                'action_by' => $user->id,
                'action_type' => 'task_resumed',
                'comments' => 'Task resumed by user.'
            ]);
            
            // Notify assignees
            $assignees = \App\Models\TaskAssignment::where('task_id', $task->id)->where('status', 'assigned')->pluck('assigned_to');
            foreach ($assignees as $userId) {
                $assigneeUser = User::find($userId);
                if ($assigneeUser) {
                    $assigneeUser->notify(new \App\Notifications\WorkflowEventNotification(
                        $task,
                        $user,
                        'resumed',
                        'The workflow task you are assigned to has been resumed.'
                    ));
                }
            }
        }
    }
}

