<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\WorkflowTransition;
use App\Services\GenericWorkflowService;
use Illuminate\Http\Request;

class GenericWorkflowInstanceController extends Controller
{
    /**
     * @var GenericWorkflowService
     */
    protected $workflowService;

    public function __construct(GenericWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Get available actions (transitions) for the specific task/workflow instance.
     * 
     * @param int|string $taskId
     */
    public function getAvailableActions($taskId)
    {
        $task = Task::findOrFail($taskId);
        $this->authorize('view', $task);
        
        if (!$task->workflow_version_id) {
            return response()->json(['error' => 'Not a generic workflow instance.'], 400);
        }

        $actions = $this->workflowService->getAvailableActions($task, auth()->user());
        $allowStepSplitting = $task->allow_step_splitting || ($task->workflowVersion->mandate_parallel_splits ?? false);

        return response()->json([
            'actions' => $actions,
            'allow_step_splitting' => $allowStepSplitting
        ]);
    }

    /**
     * Get the complete workflow state for a task.
     */
    /**
     * Get the state for a task.
     * 
     * @param int|string $taskId
     */
    public function getState($taskId)
    {
        $task = Task::with([
            'workflowLogs.user',
            'workflowLogs.attachments',
            'subTasks.workflowVersion'
        ])->findOrFail($taskId);

        if (!$task->workflow_version_id) {
            return response()->json(['error' => 'Not a generic workflow instance.'], 400);
        }

        $activeSteps = \App\Models\TaskActiveStep::with('step.checklists')
            ->where('task_id', $task->id)
            ->where('status', 'in_progress')
            ->get();

        $actions = $this->workflowService->getAvailableActions($task, auth()->user());
        $allowStepSplitting = $task->allow_step_splitting || ($task->workflowVersion->mandate_parallel_splits ?? false);

        // Fetch workflow versions for sub-workflow selection
        $workflowVersions = \App\Models\WorkflowVersion::where('is_published', true)->get();

        return response()->json([
            'active_steps' => $activeSteps,
            'available_actions' => $actions,
            'logs' => $task->workflowLogs,
            'allow_step_splitting' => $allowStepSplitting,
            'sub_tasks' => $task->subTasks,
            'workflow_versions' => $workflowVersions,
        ]);
    }

    /**
     * Execute a workflow transition action.
     */
    /**
     * Execute actions on a task.
     * 
     * @param Request $request
     * @param int|string $taskId
     */
    public function executeAction(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        $this->authorize('update', $task);
        
        if (!$task->workflow_version_id) {
            return response()->json(['error' => 'Not a generic workflow instance.'], 400);
        }

        // Normalize stringified checklists from FormData
        if ($request->has('checklists') && is_string($request->checklists)) {
            $decoded = json_decode($request->checklists, true);
            if (is_array($decoded)) {
                $request->merge(['checklists' => $decoded]);
            }
        }

        // Support target_user_id single assignment if assigned_to is omitted
        if ($request->has('target_user_id') && !$request->has('assigned_to')) {
            $targetUserId = (int)$request->target_user_id;
            if ($targetUserId > 0) {
                $request->merge(['assigned_to' => [$targetUserId]]);
            }
        }

        $request->validate([
            'transition_ids' => 'required_without:transition_id|array',
            'transition_id' => 'required_without:transition_ids',
            'assigned_to' => 'nullable|array',
            'comments' => 'nullable|string',
            'checklists' => 'nullable|array',
            'attachments.*' => 'nullable|file',
            'attachment' => 'nullable|file',
        ]);

        $transitions = collect();
        $hasAutoReturn = false;
        
        $tIds = $request->transition_ids ?? [$request->transition_id];
        
        foreach ($tIds as $tId) {
            if (strpos((string)$tId, 'auto_return_') === 0) {
                $hasAutoReturn = true;
                $transitions->push((object)[
                    'id' => $tId,
                    'action_name' => 'Return',
                    'is_auto_return' => true,
                    'from_step_id' => str_replace('auto_return_', '', $tId)
                ]);
            } else {
                $dbTrans = \App\Models\WorkflowTransition::find($tId);
                if ($dbTrans) {
                    $transitions->push($dbTrans);
                }
            }
        }

        $availableActions = $this->workflowService->getAvailableActions($task, auth()->user());
        foreach ($transitions as $transition) {
            $isAvailable = false;
            foreach ($availableActions as $action) {
                if ($action->id == $transition->id) {
                    $isAvailable = true;
                    break;
                }
            }
            if (!$isAvailable) {
                return response()->json(['error' => 'You are not authorized to perform one or more selected transitions.'], 403);
            }
        }

        try {
            $attachments = $request->file('attachments', []);
            if (empty($attachments) && $request->hasFile('attachment')) {
                $attachments = [$request->file('attachment')];
            }

            $this->workflowService->executeAction(
                $task, 
                auth()->user(), 
                $transitions, 
                $request->input('assigned_to', []), 
                $request->input('comments', ''),
                $request->input('checklists', []),
                $attachments
            );

            return response()->json(['message' => 'Action executed successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Pause a task.
     * 
     * @param Request $request
     * @param int|string $taskId
     */
    public function pauseTask(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        
        if (!$task->workflow_version_id) {
            return response()->json(['error' => 'Not a generic workflow instance.'], 400);
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Must be assigned or an admin to act
        $isAssigned = \App\Models\TaskAssignment::where('task_id', $task->id)
            ->where('assigned_to', $user->id)
            ->where('status', '!=', 'completed')
            ->exists();

        $userRoles = $user->roles->pluck('name')->map(fn($role) => strtolower($role))->toArray();
        $isAdmin = in_array('super-admin', $userRoles) || in_array('admin', $userRoles) || in_array('super user', $userRoles) || in_array('super admin', $userRoles);

        if (!$isAssigned && !$isAdmin) {
            return response()->json(['error' => 'You are not authorized to pause this task.'], 403);
        }

        try {
            $this->workflowService->pauseTask($task, auth()->user());
            return response()->json(['message' => 'Task paused successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Resume a task.
     * 
     * @param Request $request
     * @param int|string $taskId
     */
    public function resumeTask(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        
        if (!$task->workflow_version_id) {
            return response()->json(['error' => 'Not a generic workflow instance.'], 400);
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $isAssigned = \App\Models\TaskAssignment::where('task_id', $task->id)
            ->where('assigned_to', $user->id)
            ->where('status', '!=', 'completed')
            ->exists();

        $userRoles = $user->roles->pluck('name')->map(fn($role) => strtolower($role))->toArray();
        $isAdmin = in_array('super-admin', $userRoles) || in_array('admin', $userRoles) || in_array('super user', $userRoles) || in_array('super admin', $userRoles);

        if (!$isAssigned && !$isAdmin) {
            return response()->json(['error' => 'You are not authorized to resume this task.'], 403);
        }

        try {
            $this->workflowService->resumeTask($task, auth()->user());
            return response()->json(['message' => 'Task resumed successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Start a sub-workflow from an existing task.
     * 
     * @param Request $request
     * @param int|string $taskId
     */
    public function startSubWorkflow(Request $request, $taskId)
    {
        $parentTask = Task::findOrFail($taskId);
        
        if (!$parentTask->workflow_version_id) {
            return response()->json(['error' => 'Not a generic workflow instance.'], 400);
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Must be assigned or an admin to act
        $isAssigned = \App\Models\TaskAssignment::where('task_id', $parentTask->id)
            ->where('assigned_to', $user->id)
            ->where('status', '!=', 'completed')
            ->exists();

        $userRoles = $user->roles->pluck('name')->map(fn($role) => strtolower($role))->toArray();
        $isAdmin = in_array('super-admin', $userRoles) || in_array('admin', $userRoles) || in_array('super user', $userRoles) || in_array('super admin', $userRoles);

        if (!$isAssigned && !$isAdmin) {
            return response()->json(['error' => 'You are not authorized to start a sub-workflow on this task.'], 403);
        }

        $request->validate([
            'workflow_version_id' => 'required|exists:workflow_versions,id',
            'title' => 'required|string|max:255',
        ]);

        try {
            // First pause the parent task
            $this->workflowService->pauseTask($parentTask, $user);

            // Create the new sub-task
            $task = new Task();
            $task->title = $request->title;
            $task->workflow_version_id = $request->workflow_version_id;
            $task->file_id = $parentTask->file_id; // Inherit file
            $task->parent_task_id = $parentTask->id;
            $task->created_by = $user->id;
            $task->due_date = \Carbon\Carbon::now()->addDays(5)->format('Y-m-d');
            $task->save();

            // Start the sub-workflow
            $this->workflowService->start($task, $user, []);

            \App\Models\Comment::create([
                'task_id' => $parentTask->id,
                'user_id' => $user->id,
                'comment' => 'Initiated sub-workflow: ' . $task->title
            ]);

            return response()->json(['message' => 'Sub-workflow started successfully.', 'task' => $task]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
